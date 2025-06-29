<?php
namespace App\Contracts;
use App\Models\Enums\GradeLevel;

interface ScoringServiceInterface
{
    public function getGradeLevel(float $score): GradeLevel;
    public function validateScore(float $score): bool;
    public function calculateStatistics(array $scores): array;
}
