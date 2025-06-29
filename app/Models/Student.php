<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sbd',
        'ma_ngoai_ngu',
        'region_code',
        'province_code',
        'province_name',
        'exam_year',
        'is_active',
        'imported_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'exam_year' => 'integer',
        'imported_at' => 'datetime',
    ];

    /**
     * Get scores for this student
     */
    public function studentSubjectScores(): HasMany
    {
        return $this->hasMany(StudentSubjectScore::class);
    }

    /**
     * Get subjects this student has taken
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject_scores')
                    ->withPivot('score', 'grade_level', 'is_absent', 'notes')
                    ->withTimestamps();
    }

    /**
     * Scope: Active students only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By exam year
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('exam_year', $year);
    }

    /**
     * Scope: By region
     */
    public function scopeByRegion($query, $regionCode)
    {
        return $query->where('region_code', $regionCode);
    }

    /**
     * Scope: By province
     */
    public function scopeByProvince($query, $provinceCode)
    {
        return $query->where('province_code', $provinceCode);
    }

    /**
     * Get region code from SBD
     */
    public static function getRegionCodeFromSbd(string $sbd): ?string
    {
        if (strlen($sbd) < 2) {
            return null;
        }
        
        return substr($sbd, 0, 2);
    }

    /**
     * Get province mapping (basic implementation)
     */
    public static function getProvinceMapping(): array
    {
        return [
            '01' => ['code' => '01', 'name' => 'Hà Nội'],
            '02' => ['code' => '02', 'name' => 'Hà Giang'],
            '03' => ['code' => '03', 'name' => 'Cao Bằng'],
            '04' => ['code' => '04', 'name' => 'Bắc Kạn'],
            '05' => ['code' => '05', 'name' => 'Tuyên Quang'],
            // Add more provinces as needed
        ];
    }

    /**
     * Auto-detect region and province from SBD
     */
    public function detectLocationFromSbd(): void
    {
        $regionCode = self::getRegionCodeFromSbd($this->sbd);
        $provinceMapping = self::getProvinceMapping();
        
        if ($regionCode && isset($provinceMapping[$regionCode])) {
            $this->region_code = $regionCode;
            $this->province_code = $provinceMapping[$regionCode]['code'];
            $this->province_name = $provinceMapping[$regionCode]['name'];
        }
    }

    /**
     * Get student's score for a specific subject
     */
    public function getScoreForSubject(string $subjectCode): ?float
    {
        $score = $this->studentSubjectScores()
                     ->whereHas('subject', function ($query) use ($subjectCode) {
                         $query->where('code', $subjectCode);
                     })
                     ->first();
        
        return $score ? $score->score : null;
    }

    /**
     * Get Group A total score (Toán + Lý + Hóa)
     */
    public function getGroupATotalScore(): ?float
    {
        $mathScore = $this->getScoreForSubject('toan');
        $physicsScore = $this->getScoreForSubject('vat_li');
        $chemistryScore = $this->getScoreForSubject('hoa_hoc');
        
        if (is_null($mathScore) || is_null($physicsScore) || is_null($chemistryScore)) {
            return null;
        }
        
        return $mathScore + $physicsScore + $chemistryScore;
    }

    /**
     * Get all scores as array
     */
    public function getAllScores(): array
    {
        $scores = [];
        
        foreach ($this->studentSubjectScores()->with('subject')->get() as $scoreRecord) {
            $scores[$scoreRecord->subject->code] = [
                'score' => $scoreRecord->score,
                'grade_level' => $scoreRecord->grade_level,
                'subject_name' => $scoreRecord->subject->display_name,
                'is_absent' => $scoreRecord->is_absent,
            ];
        }
        
        return $scores;
    }

    /**
     * Check if student qualifies for Group A
     */
    public function qualifiesForGroupA(): bool
    {
        return !is_null($this->getGroupATotalScore());
    }

    /**
     * Get student statistics
     */
    public function getStatistics(): array
    {
        $scores = $this->studentSubjectScores()
                      ->whereNotNull('score')
                      ->pluck('score')
                      ->toArray();
        
        if (empty($scores)) {
            return [
                'total_subjects' => 0,
                'average_score' => 0,
                'max_score' => null,
                'min_score' => null,
                'group_a_total' => null,
                'qualifies_group_a' => false,
            ];
        }
        
        return [
            'total_subjects' => count($scores),
            'average_score' => round(array_sum($scores) / count($scores), 2),
            'max_score' => max($scores),
            'min_score' => min($scores),
            'group_a_total' => $this->getGroupATotalScore(),
            'qualifies_group_a' => $this->qualifiesForGroupA(),
        ];
    }
} 