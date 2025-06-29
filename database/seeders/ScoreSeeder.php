<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/diem_thi_thpt_2024.csv');

        if (!file_exists($csvPath)) {
            $this->command->error('CSV file not found');
            return;
        }

        $this->command->info('Importing sample scores from CSV...');

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        $count = 0;
        $maxRecords = 100;

        DB::transaction(function () use ($handle, &$count, $maxRecords) {
            while (($data = fgetcsv($handle)) !== false && $count < $maxRecords) {
                DB::table('scores')->insert([
                    'sbd' => $data[0],
                    'toan' => !empty($data[1]) ? (float)$data[1] : null,
                    'ngu_van' => !empty($data[2]) ? (float)$data[2] : null,
                    'ngoai_ngu' => !empty($data[3]) ? (float)$data[3] : null,
                    'vat_li' => !empty($data[4]) ? (float)$data[4] : null,
                    'hoa_hoc' => !empty($data[5]) ? (float)$data[5] : null,
                    'sinh_hoc' => !empty($data[6]) ? (float)$data[6] : null,
                    'lich_su' => !empty($data[7]) ? (float)$data[7] : null,
                    'dia_li' => !empty($data[8]) ? (float)$data[8] : null,
                    'gdcd' => !empty($data[9]) ? (float)$data[9] : null,
                    'ma_ngoai_ngu' => $data[10] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        });

        fclose($handle);

        $this->command->info("Successfully imported {$count} sample scores for testing!");
    }
}
