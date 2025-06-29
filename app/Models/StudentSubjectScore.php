<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectScore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'subject_id',
        'score',
        'grade_level',
        'is_absent',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'score' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    /**
     * Get the student that owns this score
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the subject that this score belongs to
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('score') && !is_null($model->score)) {
                $model->grade_level = Subject::getGradeLevelByScore($model->score);
            }
        });
    }

    /**
     * Scope: By student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: By subject
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope: By subject code
     */
    public function scopeBySubjectCode($query, $subjectCode)
    {
        return $query->whereHas('subject', function ($q) use ($subjectCode) {
            $q->where('code', $subjectCode);
        });
    }

    /**
     * Scope: By grade level
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope: Excellent scores (>= 8)
     */
    public function scopeExcellent($query)
    {
        return $query->where('score', '>=', 8.0);
    }

    /**
     * Scope: Good scores (>= 6 and < 8)
     */
    public function scopeGood($query)
    {
        return $query->where('score', '>=', 6.0)->where('score', '<', 8.0);
    }

    /**
     * Scope: Average scores (>= 4 and < 6)
     */
    public function scopeAverage($query)
    {
        return $query->where('score', '>=', 4.0)->where('score', '<', 6.0);
    }

    /**
     * Scope: Weak scores (< 4)
     */
    public function scopeWeak($query)
    {
        return $query->where('score', '<', 4.0);
    }

    /**
     * Scope: Not absent
     */
    public function scopeNotAbsent($query)
    {
        return $query->where('is_absent', false);
    }

    /**
     * Scope: Has score (not null)
     */
    public function scopeHasScore($query)
    {
        return $query->whereNotNull('score');
    }

    /**
     * Get grade level label
     */
    public function getGradeLevelLabelAttribute(): string
    {
        return Subject::getGradeLevelLabelByScore($this->score);
    }

    /**
     * Check if score is excellent
     */
    public function isExcellent(): bool
    {
        return $this->score >= 8.0;
    }

    /**
     * Check if score is good
     */
    public function isGood(): bool
    {
        return $this->score >= 6.0 && $this->score < 8.0;
    }

    /**
     * Check if score is average
     */
    public function isAverage(): bool
    {
        return $this->score >= 4.0 && $this->score < 6.0;
    }

    /**
     * Check if score is weak
     */
    public function isWeak(): bool
    {
        return $this->score < 4.0;
    }

    /**
     * Get formatted score with grade level
     */
    public function getFormattedScore(): string
    {
        if (is_null($this->score)) {
            return 'N/A';
        }

        return $this->score . ' (' . $this->grade_level_label . ')';
    }

    /**
     * Static method to create or update score
     */
    public static function createOrUpdateScore($studentId, $subjectId, $score, $options = [])
    {
        $data = array_merge([
            'score' => $score,
            'is_absent' => $options['is_absent'] ?? false,
            'notes' => $options['notes'] ?? null,
        ], $options);

        return static::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
            ],
            $data
        );
    }

    /**
     * Get statistics for a specific subject
     */
    public static function getSubjectStatistics($subjectId): array
    {
        $scores = static::where('subject_id', $subjectId)
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
            $level = Subject::getGradeLevelByScore($score);
            $stats[$level]++;
        }

        return $stats;
    }
}
