<?php

namespace App\Services;

use App\Contracts\ScoreServiceInterface;
use App\Contracts\ScoringServiceInterface;
use App\Contracts\SubjectServiceInterface;
use App\Models\Score;
use App\Models\Subject;

class ScoreService implements ScoreServiceInterface
{
    protected SubjectServiceInterface $subjectService;
    protected ScoringServiceInterface $scoringService;

    public function __construct(
        SubjectServiceInterface $subjectService,
        ScoringServiceInterface $scoringService
    ) {
        $this->subjectService = $subjectService;
        $this->scoringService = $scoringService;
    }
    /**
     * Find a score by student registration number.
     */
    public function findByStudentId(string $sbd): ?Score
    {
        return Score::where('sbd', $sbd)->first();
    }

    /**
     * Get statistics report for all subjects.
     */
    public function getStatisticsReport(): array
    {
        $subjects = Subject::active()->ordered()->get();
        $statistics = [];

        foreach ($subjects as $subject) {
            $stats = $this->calculateSubjectStatistics($subject);
            if ($stats['total'] > 0) {
                $statistics[] = $stats;
            }
        }

        return [
            'statistics' => $statistics,
            'summary' => $this->generateSummary()
        ];
    }

    /**
     * Get top 10 students for Group A.
     */
    public function getTop10GroupA(): array
    {
        $top10 = Score::select('sbd', 'toan', 'vat_li', 'hoa_hoc')
            ->whereNotNull('toan')
            ->whereNotNull('vat_li')
            ->whereNotNull('hoa_hoc')
            ->selectRaw('(toan + vat_li + hoa_hoc) as total_score')
            ->orderByDesc('total_score')
            ->limit(10)
            ->get();

        $rankedStudents = $top10->map(function ($item, $index) {
            $item->rank = $index + 1;
            return $item;
        });

        return [
            'top_students' => $rankedStudents,
            'group_name' => 'Khối A',
            'subjects' => ['Toán', 'Vật lý', 'Hóa học']
        ];
    }

    /**
     * Calculate statistics for a specific subject using database aggregation.
     */
    private function calculateSubjectStatistics(Subject $subject): array
    {
        $subjectCode = $subject->code;

        // Use database aggregation instead of loading all records
        $aggregates = Score::whereNotNull($subjectCode)
            ->selectRaw("
                COUNT(*) as total,
                MAX({$subjectCode}) as max_score,
                MIN({$subjectCode}) as min_score,
                AVG({$subjectCode}) as avg_score,
                SUM(CASE WHEN {$subjectCode} >= 8.0 THEN 1 ELSE 0 END) as excellent,
                SUM(CASE WHEN {$subjectCode} >= 6.0 AND {$subjectCode} < 8.0 THEN 1 ELSE 0 END) as good,
                SUM(CASE WHEN {$subjectCode} >= 4.0 AND {$subjectCode} < 6.0 THEN 1 ELSE 0 END) as average,
                SUM(CASE WHEN {$subjectCode} < 4.0 THEN 1 ELSE 0 END) as weak
            ")
            ->first();

        if (!$aggregates || $aggregates->total == 0) {
            return ['total' => 0];
        }

        $stats = [
            'total' => (int) $aggregates->total,
            'excellent' => (int) $aggregates->excellent,
            'good' => (int) $aggregates->good,
            'average' => (int) $aggregates->average,
            'weak' => (int) $aggregates->weak,
            'average_score' => round((float) $aggregates->avg_score, 2),
        ];

        // Add subject metadata
        $stats['subject_name'] = $subject->display_name;
        $stats['subject_code'] = $subject->code;
        $stats['maxScore'] = number_format((float) $aggregates->max_score, 2);
        $stats['minScore'] = number_format((float) $aggregates->min_score, 2);

        // Calculate percentages
        $stats['percentages'] = $this->calculatePercentages($stats, $stats['total']);

        return $stats;
    }

    /**
     * Calculate percentages for grade levels.
     */
    private function calculatePercentages(array $stats, int $total): array
    {
        if ($total === 0) {
            return [
                'excellent' => 0,
                'good' => 0,
                'average' => 0,
                'weak' => 0,
            ];
        }

        return [
            'excellent' => round(($stats['excellent'] / $total) * 100, 2),
            'good' => round(($stats['good'] / $total) * 100, 2),
            'average' => round(($stats['average'] / $total) * 100, 2),
            'weak' => round(($stats['weak'] / $total) * 100, 2),
        ];
    }

    /**
     * Generate summary statistics.
     */
    private function generateSummary(): array
    {
        return [
            'total_students' => Score::count(),
            'total_subjects' => Subject::active()->count(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
}
