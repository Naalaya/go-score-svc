<?php
namespace App\Models\Enums;

enum SubjectCode: string
{
    case TOAN = 'toan';
    case NGU_VAN = 'ngu_van';
    case NGOAI_NGU = 'ngoai_ngu';
    case VAT_LI = 'vat_li';
    case HOA_HOC = 'hoa_hoc';
    case SINH_HOC = 'sinh_hoc';
    case LICH_SU = 'lich_su';
    case DIA_LI = 'dia_li';
    case GDCD = 'gdcd';

    public function displayName(): string
    {
        return match($this) {
            self::TOAN => 'Toán',
            self::NGU_VAN => 'Ngữ văn',
            self::NGOAI_NGU => 'Ngoại ngữ',
            self::VAT_LI => 'Vật lý',
            self::HOA_HOC => 'Hóa học',
            self::SINH_HOC => 'Sinh học',
            self::LICH_SU => 'Lịch sử',
            self::DIA_LI => 'Địa lý',
            self::GDCD => 'GDCD',
        };
    }
}
