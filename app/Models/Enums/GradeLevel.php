<?php
namespace App\Models\Enums;

enum GradeLevel: string
{
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case AVERAGE = 'average';
    case WEAK = 'weak';

    public function label(): string
    {
        return match($this) {
            self::EXCELLENT => 'Giỏi',
            self::GOOD => 'Khá',
            self::AVERAGE => 'Trung bình',
            self::WEAK => 'Yếu',
        };
    }
}
