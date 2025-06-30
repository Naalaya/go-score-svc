<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MicroImportScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:micro-import {--batch=50 : Micro batch size} {--memory-limit=256M : Memory limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Micro import for extremely low memory environments (128MB-512MB)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🐭 Starting MICRO import for low memory environments...');

        // Set conservative memory limits
        $memoryLimit = $this->option('memory-limit');
        ini_set('memory_limit', $memoryLimit);
        ini_set('max_execution_time', 7200); // 2 hours for slower processing

        $this->info("💾 Memory limit: {$memoryLimit}");
        $this->info("🕐 Max execution: 2 hours");

        // Disable all query logging
        DB::disableQueryLog();

        $csvPath = database_path('seeders/diem_thi_thpt_2024.csv');

        if (!file_exists($csvPath)) {
            $this->error('CSV file not found!');
            return 1;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header

        $batchSize = (int) $this->option('batch');

        // Clear existing data
        $this->info("🗑️ Clearing existing scores...");
        DB::table('scores')->truncate();

        $batch = [];
        $processedCount = 0;
        $successCount = 0;
        $errorCount = 0;

        // Create progress bar without line counting (saves memory)
        $this->info("📊 Starting micro-batch processing...");

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $scoreData = $this->parseScoreData($data);

                if ($scoreData) {
                    $batch[] = $scoreData;
                    $successCount++;
                } else {
                    $errorCount++;
                }

                $processedCount++;

                // Process micro batch
                if (count($batch) >= $batchSize) {
                    $this->processMicroBatch($batch);
                    $batch = [];

                    // Memory cleanup every batch
                    gc_collect_cycles();

                    // Progress update every 1000 records
                    if ($processedCount % 1000 === 0) {
                        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
                        $this->info("📈 Processed: " . number_format($processedCount) . " | Memory: {$memoryUsage}MB");
                    }
                }
            }

            // Process final batch
            if (!empty($batch)) {
                $this->processMicroBatch($batch);
            }

            fclose($handle);

            $this->newLine();
            $this->info('🎉 MICRO IMPORT COMPLETED!');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Processed', number_format($processedCount)],
                    ['Successfully Imported', number_format($successCount)],
                    ['Errors/Skipped', number_format($errorCount)],
                    ['Final Database Count', number_format(DB::table('scores')->count())],
                    ['Peak Memory Usage', round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'],
                ]
            );

        } catch (\Exception $e) {
            $this->error('❌ Micro import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function parseScoreData(array $data): ?array
    {
        $sbd = trim($data[0] ?? '');

        if (empty($sbd) || !preg_match('/^\d{8,10}$/', $sbd)) {
            return null;
        }

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

    private function processMicroBatch(array $batch): void
    {
        // Process in tiny chunks to minimize memory usage
        $tinyChunks = array_chunk($batch, 10); // Only 10 records at a time

        foreach ($tinyChunks as $chunk) {
            DB::table('scores')->insert($chunk);
            unset($chunk); // Immediate cleanup
        }

        unset($tinyChunks, $batch);
    }
}
