<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'display_name',
        'group_code',
        'group_name',
        'order',
        'is_active',
        'is_required',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get scores for this subject
     */
    public function studentSubjectScores(): HasMany
    {
        return $this->hasMany(StudentSubjectScore::class);
    }

    /**
     * Get students who have taken this subject
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject_scores')
                    ->withPivot('score', 'grade_level', 'is_absent', 'notes')
                    ->withTimestamps();
    }

    /**
     * Scope: Active subjects only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Required subjects only
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope: By group
     */
    public function scopeByGroup($query, $groupCode)
    {
        return $query->where('group_code', $groupCode);
    }

    /**
     * Scope: Order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('display_name');
    }

    /**
     * Get grade level constants
     */
    public static function getGradeLevels(): array
    {
        return [
            'excellent' => ['label' => 'Giỏi', 'min_score' => 8.0],
            'good' => ['label' => 'Khá', 'min_score' => 6.0],
            'average' => ['label' => 'Trung bình', 'min_score' => 4.0],
            'weak' => ['label' => 'Yếu', 'min_score' => 0.0],
        ];
    }

    /**
     * Get grade level by score
     */
    public static function getGradeLevelByScore(?float $score): string
    {
        if (is_null($score)) {
            return 'N/A';
        }

        if ($score >= 8.0) return 'excellent';
        if ($score >= 6.0) return 'good';
        if ($score >= 4.0) return 'average';
        return 'weak';
    }

    /**
     * Get grade level label by score
     */
    public static function getGradeLevelLabelByScore(?float $score): string
    {
        $levels = self::getGradeLevels();
        $level = self::getGradeLevelByScore($score);

        return $levels[$level]['label'] ?? 'N/A';
    }

    /**
     * Get subject groups
     */
    public static function getGroups(): array
    {
        return [
            'A' => 'Khối A (Toán, Lý, Hóa)',
            'B' => 'Khối B (Toán, Hóa, Sinh)',
            'C' => 'Khối C (Văn, Sử, Địa)',
            'D' => 'Khối D (Văn, Toán, Anh)',
        ];
    }

    /**
     * Check if subject belongs to Group A
     */
    public function isGroupA(): bool
    {
        return $this->group_code === 'A';
    }

    /**
     * Get statistics for this subject
     */
    public function getStatistics(): array
    {
        $scores = $this->studentSubjectScores()
                      ->whereNotNull('score')
                      ->pluck('score')
                      ->toArray();

        if (empty($scores)) {
            return [
                'total' => 0,
                'excellent' => 0,
                'good' => 0,
                'average' => 0,
                'weak' => 0,
                'average_score' => 0,
                'max_score' => null,
                'min_score' => null,
            ];
        }

        $stats = [
            'total' => count($scores),
            'excellent' => 0,
            'good' => 0,
            'average' => 0,
            'weak' => 0,
            'average_score' => round(array_sum($scores) / count($scores), 2),
            'max_score' => max($scores),
            'min_score' => min($scores),
        ];

        foreach ($scores as $score) {
            $level = self::getGradeLevelByScore($score);
            $stats[$level]++;
        }

        return $stats;
    }
}
