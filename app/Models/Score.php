<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'sbd',
        'toan',
        'ngu_van',
        'ngoai_ngu',
        'vat_li',
        'hoa_hoc',
        'sinh_hoc',
        'lich_su',
        'dia_li',
        'gdcd',
        'ma_ngoai_ngu',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'toan' => 'decimal:2',
        'ngu_van' => 'decimal:2',
        'ngoai_ngu' => 'decimal:2',
        'vat_li' => 'decimal:2',
        'hoa_hoc' => 'decimal:2',
        'sinh_hoc' => 'decimal:2',
        'lich_su' => 'decimal:2',
        'dia_li' => 'decimal:2',
        'gdcd' => 'decimal:2',
    ];

    /**
     * Tính tổng điểm khối A (Toán, Lý, Hóa)
     */
    public function getTotalGroupAAttribute(): ?float
    {
        if (is_null($this->toan) || is_null($this->vat_li) || is_null($this->hoa_hoc)) {
            return null;
        }

        return $this->toan + $this->vat_li + $this->hoa_hoc;
    }

    /**
     * Get all subject scores as array
     */
    public function getSubjectScores(): array
    {
        return [
            'toan' => $this->toan,
            'ngu_van' => $this->ngu_van,
            'ngoai_ngu' => $this->ngoai_ngu,
            'vat_li' => $this->vat_li,
            'hoa_hoc' => $this->hoa_hoc,
            'sinh_hoc' => $this->sinh_hoc,
            'lich_su' => $this->lich_su,
            'dia_li' => $this->dia_li,
            'gdcd' => $this->gdcd,
        ];
    }
}
