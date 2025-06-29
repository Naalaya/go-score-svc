<?php
return [
    'subjects' => [
        'toan' => ['display_name' => 'Toán', 'group_code' => 'A', 'order' => 1, 'is_required' => true, 'is_active' => true],
        'ngu_van' => ['display_name' => 'Ngữ văn', 'group_code' => null, 'order' => 2, 'is_required' => true, 'is_active' => true],
        'ngoai_ngu' => ['display_name' => 'Ngoại ngữ', 'group_code' => null, 'order' => 3, 'is_required' => true, 'is_active' => true],
        'vat_li' => ['display_name' => 'Vật lý', 'group_code' => 'A', 'order' => 4, 'is_required' => false, 'is_active' => true],
        'hoa_hoc' => ['display_name' => 'Hóa học', 'group_code' => 'A', 'order' => 5, 'is_required' => false, 'is_active' => true],
        'sinh_hoc' => ['display_name' => 'Sinh học', 'group_code' => 'B', 'order' => 6, 'is_required' => false, 'is_active' => true],
        'lich_su' => ['display_name' => 'Lịch sử', 'group_code' => 'C', 'order' => 7, 'is_required' => false, 'is_active' => true],
        'dia_li' => ['display_name' => 'Địa lý', 'group_code' => 'C', 'order' => 8, 'is_required' => false, 'is_active' => true],
        'gdcd' => ['display_name' => 'GDCD', 'group_code' => 'D', 'order' => 9, 'is_required' => false, 'is_active' => true],
    ],
    'groups' => [
        'A' => ['name' => 'Khối A', 'description' => 'Khối Toán - Lý - Hóa', 'subjects' => ['toan', 'vat_li', 'hoa_hoc']],
        'B' => ['name' => 'Khối B', 'description' => 'Khối Toán - Hóa - Sinh', 'subjects' => ['toan', 'hoa_hoc', 'sinh_hoc']],
        'C' => ['name' => 'Khối C', 'description' => 'Khối Văn - Sử - Địa', 'subjects' => ['ngu_van', 'lich_su', 'dia_li']],
        'D' => ['name' => 'Khối D', 'description' => 'Khối Văn - Toán - Anh', 'subjects' => ['ngu_van', 'toan', 'ngoai_ngu']],
    ],
];
