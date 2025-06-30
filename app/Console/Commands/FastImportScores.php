<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;

class FastImportScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:fast-import {--batch=500 : Batch size} {--chunk=2500 : Chunk size} {--memory-limit=1G : Memory limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fast import all scores from CSV for production use';

    /**
     * Execute the console command.
     */
        public function handle()
    {
        $this->info('Starting FAST import for production...');

        // Set aggressive memory limits
        $memoryLimit = $this->option('memory-limit');
        ini_set('memory_limit', $memoryLimit);
        ini_set('max_execution_time', 3600); // 1 hour

        $this->info("Memory limit set to: {$memoryLimit}");
        $this->info("Max execution time: 1 hour");

        // Disable query logging to save memory
        DB::disableQueryLog();

        $csvPath = database_path('seeders/diem_thi_thpt_2024.csv');

        if (!file_exists($csvPath)) {
            $this->error('CSV file not found!');
            return 1;
        }

        // Get file size for progress tracking
        $totalLines = $this->getLineCount($csvPath) - 1; // Exclude header
        $this->info("Total records to import: " . number_format($totalLines));

        // Load subjects mapping
        $subjects = Subject::pluck('id', 'code')->toArray();
        $this->info('Loaded ' . count($subjects) . ' subjects');

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header

        $batchSize = (int) $this->option('batch');
        $chunkSize = (int) $this->option('chunk');

        $bar = $this->output->createProgressBar($totalLines);
        $bar->setFormat('verbose');
        $bar->start();

        $scoresBatch = [];
        $processedCount = 0;
        $successCount = 0;
        $errorCount = 0;

        // Clear existing data first
        $this->info("\nClearing existing scores...");
        DB::table('scores')->truncate();

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $scoreData = $this->parseScoreData($data, $subjects);

                if ($scoreData) {
                    $scoresBatch[] = $scoreData;
                    $successCount++;
                } else {
                    $errorCount++;
                }

                $processedCount++;

                                // Insert batch when reaching batch size
                if (count($scoresBatch) >= $batchSize) {
                    $this->insertBatch($scoresBatch);
                    $scoresBatch = [];

                    // Aggressive memory cleanup every batch
                    gc_collect_cycles();

                    // Memory cleanup and stats every chunk
                    if ($processedCount % $chunkSize === 0) {
                        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
                        $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
                        $this->info("\n💾 Memory: {$memoryUsage}MB | Peak: {$peakMemory}MB | Records: " . number_format($processedCount));
                    }
                }

                $bar->advance();
            }

            // Insert remaining batch
            if (!empty($scoresBatch)) {
                $this->insertBatch($scoresBatch);
            }

            DB::commit();
            $bar->finish();

            $this->newLine(2);
            $this->info('IMPORT COMPLETED SUCCESSFULLY!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Processed', number_format($processedCount)],
                    ['Successfully Imported', number_format($successCount)],
                    ['Errors/Skipped', number_format($errorCount)],
                    ['Final Database Count', number_format(DB::table('scores')->count())],
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        } finally {
            fclose($handle);
        }

        return 0;
    }

    private function getLineCount(string $filePath): int
    {
        $this->info('📏 Counting lines in CSV...');
        $lineCount = 0;
        $handle = fopen($filePath, 'r');

        while (fgets($handle) !== false) {
            $lineCount++;
        }

        fclose($handle);
        return $lineCount;
    }

    private function parseScoreData(array $data, array $subjects): ?array
    {
        $sbd = trim($data[0] ?? '');

        if (empty($sbd) || !preg_match('/^\d{8,10}$/', $sbd)) {
            return null;
        }

        // Parse scores with null handling
        $parseScore = function($value) {
            $value = trim($value);
            if (empty($value) || $value === '' || $value === 'null') {
                return null;
            }
            return (float) str_replace(',', '.', $value);
        };

        return [
            'sbd' => $sbd,
            'toan' => $parseScore($data[1] ?? ''),
            'ngu_van' => $parseScore($data[2] ?? ''),
            'ngoai_ngu' => $parseScore($data[3] ?? ''),
            'vat_li' => $parseScore($data[4] ?? ''),
            'hoa_hoc' => $parseScore($data[5] ?? ''),
            'sinh_hoc' => $parseScore($data[6] ?? ''),
            'lich_su' => $parseScore($data[7] ?? ''),
            'dia_li' => $parseScore($data[8] ?? ''),
            'gdcd' => $parseScore($data[9] ?? ''),
            'ma_ngoai_ngu' => trim($data[10] ?? '') ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

        private function insertBatch(array $batch): void
    {
        // Use smaller chunks to reduce memory usage
        $chunkSize = min(250, count($batch)); // Smaller chunks for memory efficiency
        $chunks = array_chunk($batch, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                DB::table('scores')->insert($chunk);

                // Immediate memory cleanup after each chunk
                unset($chunk);
            } catch (\Exception $e) {
                $this->error("Failed to insert chunk: " . $e->getMessage());
                throw $e;
            }
        }

        // Final cleanup
        unset($chunks, $batch);
        gc_collect_cycles();
    }
}
