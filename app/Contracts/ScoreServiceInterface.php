<?php

namespace App\Contracts;

use App\Models\Score;

interface ScoreServiceInterface
{
    /**
     * Find a score by student registration number.
     */
    public function findByStudentId(string $sbd): ?Score;

    /**
     * Get statistics report for all subjects.
     */
    public function getStatisticsReport(): array;

    /**
     * Get top 10 students for Group A.
     */
    public function getTop10GroupA(): array;
}
