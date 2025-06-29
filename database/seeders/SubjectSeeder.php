<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'toan', 'display_name' => 'Toán', 'group_code' => null, 'order' => 1],
            ['code' => 'ngu_van', 'display_name' => 'Ngữ văn', 'group_code' => null, 'order' => 2],
            ['code' => 'ngoai_ngu', 'display_name' => 'Ngoại ngữ', 'group_code' => null, 'order' => 3],

            ['code' => 'vat_li', 'display_name' => 'Vật lý', 'group_code' => 'A', 'order' => 4],
            ['code' => 'hoa_hoc', 'display_name' => 'Hóa học', 'group_code' => 'A', 'order' => 5],

            ['code' => 'sinh_hoc', 'display_name' => 'Sinh học', 'group_code' => 'B', 'order' => 6],
            ['code' => 'lich_su', 'display_name' => 'Lịch sử', 'group_code' => 'C', 'order' => 7],
            ['code' => 'dia_li', 'display_name' => 'Địa lý', 'group_code' => 'C', 'order' => 8],
            ['code' => 'gdcd', 'display_name' => 'GDCD', 'group_code' => 'D', 'order' => 9],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['code' => $subject['code']],
                array_merge($subject, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Subjects seeded successfully!');
    }
}
