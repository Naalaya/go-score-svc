<?php
namespace App\Services\Subjects;
use App\Contracts\ScoringServiceInterface;
use App\Models\Enums\GradeLevel;

class ScoringService implements ScoringServiceInterface
{
    public function getGradeLevel(float $score): GradeLevel
    {
        if ($score >= 8.0) return GradeLevel::EXCELLENT;
        if ($score >= 6.0) return GradeLevel::GOOD;
        if ($score >= 4.0) return GradeLevel::AVERAGE;
        return GradeLevel::WEAK;
    }

    public function validateScore(float $score): bool
    {
        return $score >= 0.0 && $score <= 10.0;
    }

    public function calculateStatistics(array $scores): array
    {
        if (empty($scores)) {
            return ['total' => 0, 'excellent' => 0, 'good' => 0, 'average' => 0, 'weak' => 0];
        }

        $stats = [
            'total' => count($scores),
            'excellent' => 0,
            'good' => 0,
            'average' => 0,
            'weak' => 0,
            'average_score' => round(array_sum($scores) / count($scores), 2),
        ];

        foreach ($scores as $score) {
            $level = $this->getGradeLevel($score);
            $stats[$level->value]++;
        }

        return $stats;
    }
}
