<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Score;
use App\Models\Subject;

class ImportController extends Controller
{
    /**
     * Get import status and statistics
     */
    public function status(): JsonResponse
    {
        $csvPath = database_path('seeders/diem_thi_thpt_2024.csv');
        $csvExists = file_exists($csvPath);
        $csvSize = $csvExists ? filesize($csvPath) : 0;
        $csvLines = $csvExists ? $this->countLines($csvPath) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'csv_file' => [
                    'exists' => $csvExists,
                    'size_mb' => round($csvSize / 1024 / 1024, 2),
                    'total_lines' => $csvLines,
                    'estimated_records' => max(0, $csvLines - 1), // Exclude header
                ],
                'database' => [
                    'subjects_count' => Subject::count(),
                    'scores_count' => Score::count(),
                    'last_updated' => Score::latest('updated_at')->value('updated_at'),
                ],
                'memory_info' => [
                    'memory_limit' => ini_get('memory_limit'),
                    'memory_limit_mb' => $this->parseMemoryLimit(ini_get('memory_limit')),
                    'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                    'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
                    'recommended_command' => $this->parseMemoryLimit(ini_get('memory_limit')) <= 256 ? 'scores:micro-import' : 'scores:fast-import',
                    'recommended_settings' => $this->getRecommendedSettings(),
                ],
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'server_time' => now()->toISOString(),
                    'timezone' => config('app.timezone'),
                ]
            ]
        ]);
    }

    /**
     * Trigger fast import (use with caution in production)
     */
    public function triggerImport(Request $request): JsonResponse
    {
        // Security check - only allow in specific conditions
        if (app()->environment('production') && !$request->has('confirm_production')) {
            return response()->json([
                'success' => false,
                'message' => 'Production import requires confirmation parameter: confirm_production=true',
                'warning' => 'This operation will replace all existing data and may take 15-30 minutes'
            ], 400);
        }

        $csvPath = database_path('seeders/diem_thi_thpt_2024.csv');
        if (!file_exists($csvPath)) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file not found'
            ], 404);
        }

        // Auto-detect optimal settings based on current memory limit
        $currentMemoryLimit = ini_get('memory_limit');
        $memoryInMB = $this->parseMemoryLimit($currentMemoryLimit);

        // Auto-select command and parameters based on available memory
        if ($memoryInMB <= 256) {
            // Use micro import for low memory environments
            $command = 'scores:micro-import';
            $batch = (int) ($request->input('batch', 25));
            $memoryLimit = $request->input('memory_limit', '256M');
            $params = [
                '--batch' => $batch,
                '--memory-limit' => $memoryLimit,
            ];
        } else {
            // Use fast import for normal memory environments
            $command = 'scores:fast-import';
            $batch = (int) ($request->input('batch', 300));
            $chunk = (int) ($request->input('chunk', 1500));
            $memoryLimit = $request->input('memory_limit', '512M');
            $params = [
                '--batch' => $batch,
                '--chunk' => $chunk,
                '--memory-limit' => $memoryLimit,
            ];
        }

        try {
            // Run import with auto-selected command and parameters
            $exitCode = Artisan::call($command, $params);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import completed successfully',
                    'data' => [
                        'final_count' => Score::count(),
                        'subjects_count' => Subject::count(),
                        'import_time' => now()->toISOString(),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed with exit code: ' . $exitCode,
                    'output' => Artisan::output()
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all scores data (use with extreme caution)
     */
    public function clearData(Request $request): JsonResponse
    {
        if (app()->environment('production') && !$request->has('confirm_clear')) {
            return response()->json([
                'success' => false,
                'message' => 'Production data clear requires confirmation parameter: confirm_clear=true',
                'warning' => 'This operation will permanently delete all score data'
            ], 400);
        }

        try {
            $deletedCount = Score::count();
            DB::table('scores')->truncate();

            return response()->json([
                'success' => true,
                'message' => 'Data cleared successfully',
                'data' => [
                    'deleted_records' => $deletedCount,
                    'remaining_records' => Score::count(),
                    'cleared_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sample data import (for testing)
     */
    public function importSample(): JsonResponse
    {
        try {
            $exitCode = Artisan::call('db:seed', [
                '--class' => 'ScoreSeeder',
                '--force' => true
            ]);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sample data imported successfully',
                    'data' => [
                        'sample_count' => 100,
                        'total_scores' => Score::count(),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sample import failed'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sample import failed: ' . $e->getMessage()
            ], 500);
        }
    }

        /**
     * Get recommended import settings based on current memory
     */
    private function getRecommendedSettings(): array
    {
        $memoryInMB = $this->parseMemoryLimit(ini_get('memory_limit'));

        if ($memoryInMB <= 128) {
            return [
                'command' => 'scores:micro-import',
                'batch' => 15,
                'memory_limit' => '128M',
                'estimated_time' => '2-4 hours',
                'description' => 'Ultra-conservative for 128M memory'
            ];
        } elseif ($memoryInMB <= 256) {
            return [
                'command' => 'scores:micro-import',
                'batch' => 25,
                'memory_limit' => '256M',
                'estimated_time' => '1.5-3 hours',
                'description' => 'Safe for 256M memory'
            ];
        } elseif ($memoryInMB <= 512) {
            return [
                'command' => 'scores:fast-import',
                'batch' => 200,
                'chunk' => 1000,
                'memory_limit' => '512M',
                'estimated_time' => '30-60 minutes',
                'description' => 'Moderate for 512M memory'
            ];
        } else {
            return [
                'command' => 'scores:fast-import',
                'batch' => 500,
                'chunk' => 2500,
                'memory_limit' => '1G',
                'estimated_time' => '15-30 minutes',
                'description' => 'Fast for 1G+ memory'
            ];
        }
    }

    /**
     * Parse memory limit string to MB
     */
    private function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = strtoupper(trim($memoryLimit));

        // Extract numeric value
        $numeric = (int) preg_replace('/[^0-9]/', '', $memoryLimit);

        // Convert to MB based on suffix
        if (strpos($memoryLimit, 'G') !== false) {
            return $numeric * 1024; // GB to MB
        } elseif (strpos($memoryLimit, 'M') !== false) {
            return $numeric; // Already in MB
        } elseif (strpos($memoryLimit, 'K') !== false) {
            return max(1, $numeric / 1024); // KB to MB
        } else {
            // Assume bytes, convert to MB
            return max(1, $numeric / 1024 / 1024);
        }
    }

    /**
     * Count lines in file efficiently
     */
    private function countLines(string $filePath): int
    {
        $lineCount = 0;
        $handle = fopen($filePath, 'r');

        if ($handle) {
            while (fgets($handle) !== false) {
                $lineCount++;
            }
            fclose($handle);
        }

        return $lineCount;
    }
}
