<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Subject;
use App\Models\StudentSubjectScore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCsvToNormalized extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scores:import-normalized {--path=database/seeders/diem_thi_thpt_2024.csv : Path to CSV file} {--batch=500 : Batch size for processing}';

    /**
     * The console command description.
     */
    protected $description = 'Import scores from CSV to normalized database structure';

    /**
     * Subject mapping cache
     */
    private array $subjectMapping = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path($this->option('path'));
        $batchSize = (int) $this->option('batch');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info('Starting normalized import from CSV...');

        $this->loadSubjectMapping();

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error('Cannot open CSV file');
            return 1;
        }

        $header = fgetcsv($handle);
        $columnMapping = $this->getColumnMapping($header);

        $lineCount = count(file($path)) - 1;
        $bar = $this->output->createProgressBar($lineCount);
        $bar->start();

        $studentsBuffer = [];
        $scoresBuffer = [];
        $processedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $studentData = $this->parseStudentData($data, $columnMapping);

                if (!$studentData) {
                    $skippedCount++;
                    $bar->advance();
                                        continue;
                }

                $studentsBuffer[] = $studentData['student'];

                foreach ($studentData['scores'] as $score) {
                    $scoresBuffer[] = $score;
                }

                $processedCount++;

                if (count($studentsBuffer) >= $batchSize) {
                    $this->processBatch($studentsBuffer, $scoresBuffer);
                    $studentsBuffer = [];
                    $scoresBuffer = [];
                }

                $bar->advance();
            }

            if (!empty($studentsBuffer)) {
                $this->processBatch($studentsBuffer, $scoresBuffer);
            }

            DB::commit();
            $bar->finish();

            $this->newLine(2);
            $this->info("Normalized import completed successfully!");
            $this->info("Total processed: {$processedCount}");
            $this->info("Total skipped: {$skippedCount}");
            $this->info("Total students: " . Student::count());
            $this->info("Total scores: " . StudentSubjectScore::count());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine(2);
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        } finally {
            fclose($handle);
        }

        return 0;
    }

    /**
     * Load subject mapping for performance
     */
    private function loadSubjectMapping(): void
    {
        $subjects = Subject::all();
        foreach ($subjects as $subject) {
            $this->subjectMapping[$subject->code] = $subject->id;
        }

        $this->info('Loaded ' . count($this->subjectMapping) . ' subjects');
    }

    /**
     * Get column mapping from header
     */
    private function getColumnMapping(array $header): array
    {
        return [
            'sbd' => 0,
            'toan' => 1,
            'ngu_van' => 2,
            'ngoai_ngu' => 3,
            'vat_li' => 4,
            'hoa_hoc' => 5,
            'sinh_hoc' => 6,
            'lich_su' => 7,
            'dia_li' => 8,
            'gdcd' => 9,
            'ma_ngoai_ngu' => 10,
        ];
    }

    /**
     * Parse student data from CSV row
     */
    private function parseStudentData(array $data, array $columnMapping): ?array
    {
        $sbd = $data[$columnMapping['sbd']] ?? '';

        if (empty($sbd)) {
            return null;
        }

        $studentData = [
            'sbd' => $sbd,
            'ma_ngoai_ngu' => $data[$columnMapping['ma_ngoai_ngu']] ?? null,
            'region_code' => Student::getRegionCodeFromSbd($sbd),
            'exam_year' => 2024,
            'is_active' => true,
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $provinceMapping = Student::getProvinceMapping();
        $regionCode = $studentData['region_code'];
        if ($regionCode && isset($provinceMapping[$regionCode])) {
            $studentData['province_code'] = $provinceMapping[$regionCode]['code'];
            $studentData['province_name'] = $provinceMapping[$regionCode]['name'];
        }

        $scores = [];
        $subjectColumns = ['toan', 'ngu_van', 'ngoai_ngu', 'vat_li', 'hoa_hoc', 'sinh_hoc', 'lich_su', 'dia_li', 'gdcd'];

        foreach ($subjectColumns as $subjectCode) {
            if (!isset($this->subjectMapping[$subjectCode])) {
                continue;
            }

            $scoreValue = $data[$columnMapping[$subjectCode]] ?? '';
            $score = $this->parseScore($scoreValue);

            if ($score !== null || !empty($scoreValue)) {
                $scores[] = [
                    'sbd' => $sbd,
                    'subject_id' => $this->subjectMapping[$subjectCode],
                    'score' => $score,
                    'grade_level' => $score ? Subject::getGradeLevelByScore($score) : null,
                    'is_absent' => is_null($score) && !empty($scoreValue),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return [
            'student' => $studentData,
            'scores' => $scores
        ];
    }

    /**
     * Parse score value
     */
    private function parseScore($value): ?float
    {
        if (empty($value) || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', $value);
    }

    /**
     * Process batch of students and scores
     */
    private function processBatch(array $studentsBuffer, array $scoresBuffer): void
    {
        $studentIds = [];
        foreach ($studentsBuffer as $studentData) {
            $student = Student::firstOrCreate(
                ['sbd' => $studentData['sbd']],
                $studentData
            );
            $studentIds[$studentData['sbd']] = $student->id;
        }

        $finalScores = [];
        foreach ($scoresBuffer as $scoreData) {
            if (isset($studentIds[$scoreData['sbd']])) {
                $scoreData['student_id'] = $studentIds[$scoreData['sbd']];
                unset($scoreData['sbd']);
                $finalScores[] = $scoreData;
            }
        }

        if (!empty($finalScores)) {
            $chunks = array_chunk($finalScores, 100);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $scoreData) {
                    StudentSubjectScore::updateOrCreate(
                        [
                            'student_id' => $scoreData['student_id'],
                            'subject_id' => $scoreData['subject_id'],
                        ],
                        $scoreData
                    );
                }
            }
        }
    }
}
