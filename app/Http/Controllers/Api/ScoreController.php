<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchScoreRequest;
use App\Http\Requests\Api\StatisticsRequest;
use App\Http\Resources\ScoreResource;
use App\Contracts\ScoreServiceInterface;

class ScoreController extends Controller
{
    /**
     * Score service instance.
     */
    private ScoreServiceInterface $scoreService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ScoreServiceInterface $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    /**
     * Search score by student ID (SBD).
     *
     * Requirement: Check score from registration number input
     */
    public function searchByStudentId(SearchScoreRequest $request)
    {
        $score = $this->scoreService->findByStudentId($request->getSbd());

        if (!$score) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy số báo danh này'
            ], 404);
        }

        $data = new ScoreResource($score);

        // Add enhanced features from Phase 2
        if ($request->shouldIncludeStatistics()) {
            $data->additional(['statistics' => $this->scoreService->getStatisticsReport()]);
        }

        if ($request->shouldIncludeMetadata()) {
            $data->additional([
                'metadata' => [
                    'search_year' => $request->getYear(),
                    'searched_at' => now()->toISOString(),
                    'api_version' => '2.0'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get statistics report for all subjects.
     *
     * Requirement: 4-level statistics report (>=8, 6-8, 4-6, <4 points)
     */
    public function getStatisticsReport(StatisticsRequest $request)
    {
        $data = $this->scoreService->getStatisticsReport();

        // Apply filters from Phase 2 validation
        if ($request->has('group_code')) {
            $data['filtered_by_group'] = $request->input('group_code');
        }

        if ($request->has('subject_codes')) {
            $data['filtered_subjects'] = $request->input('subject_codes');
        }

        if ($request->input('include_percentages', false)) {
            // Add percentage calculations for each statistic
            foreach ($data['statistics'] as &$stat) {
                if ($stat['total'] > 0) {
                    $stat['percentages'] = [
                        'excellent' => round(($stat['excellent'] / $stat['total']) * 100, 2),
                        'good' => round(($stat['good'] / $stat['total']) * 100, 2),
                        'average' => round(($stat['average'] / $stat['total']) * 100, 2),
                        'weak' => round(($stat['weak'] / $stat['total']) * 100, 2),
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'api_version' => '2.0'
        ]);
    }

    /**
     * Get top 10 students of Group A.
     *
     * Requirement: Top 10 students of Group A (math, physics, chemistry)
     */
    public function getTop10GroupA()
    {
        $data = $this->scoreService->getTop10GroupA();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


}
