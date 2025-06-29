<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\StudentSubjectScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NormalizedScoreController extends Controller
{
    /**
     * Search score by student ID (SBD) - Normalized version
     */
    public function searchByStudentId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sbd' => 'required|string|min:8|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::with(['studentSubjectScores.subject'])
                          ->where('sbd', $request->sbd)
                          ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy số báo danh này'
            ], 404);
        }

        $scores = [];
        $gradeLevels = [];

        foreach ($student->studentSubjectScores as $scoreRecord) {
            $subjectCode = $scoreRecord->subject->code;
            $scores[$subjectCode] = $scoreRecord->score;

            $gradeLevels[$subjectCode] = [
                'score' => $scoreRecord->score,
                'level' => $scoreRecord->grade_level_label,
                'display_name' => $scoreRecord->subject->display_name,
                'is_absent' => $scoreRecord->is_absent,
            ];
        }

        $responseData = [
            'id' => $student->id,
            'sbd' => $student->sbd,
            'ma_ngoai_ngu' => $student->ma_ngoai_ngu,
            'region_code' => $student->region_code,
            'province_name' => $student->province_name,
            'exam_year' => $student->exam_year,
            'scores' => $scores,
            'grade_levels' => $gradeLevels,
            'statistics' => $student->getStatistics(),
            'total_group_a' => $student->getGroupATotalScore(),
            'qualifies_group_a' => $student->qualifiesForGroupA(),
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    /**
     * Get statistics report for all subjects - Normalized version
     */
    public function getStatisticsReport()
    {
        $subjects = Subject::active()->ordered()->get();
        $statistics = [];

        foreach ($subjects as $subject) {
                        $stats = $subject->getStatistics();

            if ($stats['total'] > 0) {
                $stats['percentages'] = [
                    'excellent' => round(($stats['excellent'] / $stats['total']) * 100, 2),
                    'good' => round(($stats['good'] / $stats['total']) * 100, 2),
                    'average' => round(($stats['average'] / $stats['total']) * 100, 2),
                    'weak' => round(($stats['weak'] / $stats['total']) * 100, 2),
                ];

                $stats['subject_info'] = [
                    'code' => $subject->code,
                    'name' => $subject->display_name,
                    'group' => $subject->group_name,
                    'is_required' => $subject->is_required,
                ];

                $statistics[] = $stats;
            }
        }

        $summary = [
            'total_students' => Student::count(),
            'total_subjects' => Subject::active()->count(),
            'total_scores' => StudentSubjectScore::hasScore()->count(),
            'subjects_by_group' => $this->getSubjectsByGroup(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Get top 10 students of Group A - Normalized version
     */
        public function getTop10GroupA()
    {
        $groupASubjects = Subject::byGroup('A')->pluck('id')->toArray();

        if (count($groupASubjects) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Không đủ môn học khối A'
            ], 400);
        }

        $top10 = Student::select('students.*')
            ->selectRaw('
                SUM(CASE WHEN subjects.code = "toan" THEN student_subject_scores.score ELSE 0 END) as toan,
                SUM(CASE WHEN subjects.code = "vat_li" THEN student_subject_scores.score ELSE 0 END) as vat_li,
                SUM(CASE WHEN subjects.code = "hoa_hoc" THEN student_subject_scores.score ELSE 0 END) as hoa_hoc,
                (SUM(CASE WHEN subjects.code = "toan" THEN student_subject_scores.score ELSE 0 END) +
                 SUM(CASE WHEN subjects.code = "vat_li" THEN student_subject_scores.score ELSE 0 END) +
                 SUM(CASE WHEN subjects.code = "hoa_hoc" THEN student_subject_scores.score ELSE 0 END)) as total_score
            ')
            ->join('student_subject_scores', 'students.id', '=', 'student_subject_scores.student_id')
            ->join('subjects', 'student_subject_scores.subject_id', '=', 'subjects.id')
            ->whereIn('subjects.code', ['toan', 'vat_li', 'hoa_hoc'])
            ->whereNotNull('student_subject_scores.score')
            ->groupBy('students.id', 'students.sbd', 'students.ma_ngoai_ngu', 'students.region_code', 'students.province_code', 'students.province_name', 'students.exam_year', 'students.is_active', 'students.imported_at', 'students.created_at', 'students.updated_at')
            ->having(DB::raw('COUNT(student_subject_scores.id)'), '=', 3)
            ->orderByDesc('total_score')
            ->limit(10)
            ->get();

        $top10 = $top10->map(function ($student, $index) {
            return [
                'rank' => $index + 1,
                'sbd' => $student->sbd,
                'toan' => $student->toan,
                'vat_li' => $student->vat_li,
                'hoa_hoc' => $student->hoa_hoc,
                'total_score' => $student->total_score,
                'region_code' => $student->region_code,
                'province_name' => $student->province_name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'top_students' => $top10,
                'group_name' => 'Khối A',
                'subjects' => ['Toán', 'Vật lý', 'Hóa học'],
                'total_qualified' => Student::whereHas('studentSubjectScores', function ($query) {
                    $query->whereHas('subject', function ($q) {
                        $q->whereIn('code', ['toan', 'vat_li', 'hoa_hoc']);
                    })
                    ->whereNotNull('score');
                })->count()
            ]
        ]);
    }

    /**
     * Get score distribution for a specific subject - Normalized version
     */
    public function getSubjectDistribution(Request $request, $subjectCode)
    {
        $subject = Subject::where('code', $subjectCode)->first();

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Môn học không hợp lệ'
            ], 400);
        }

        $distribution = StudentSubjectScore::select(
                DB::raw("FLOOR(score) as score_range"),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(score) as avg_score_in_range')
            )
            ->where('subject_id', $subject->id)
            ->whereNotNull('score')
            ->groupBy('score_range')
            ->orderBy('score_range')
            ->get();

        $gradeLevelDistribution = StudentSubjectScore::select('grade_level', DB::raw('COUNT(*) as count'))
            ->where('subject_id', $subject->id)
            ->whereNotNull('grade_level')
            ->groupBy('grade_level')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'subject' => [
                    'code' => $subject->code,
                    'name' => $subject->display_name,
                    'group' => $subject->group_name,
                ],
                'score_distribution' => $distribution,
                'grade_distribution' => $gradeLevelDistribution,
                'statistics' => $subject->getStatistics()
            ]
        ]);
    }

    /**
     * Get detailed analytics - New endpoint
     */
    public function getDetailedAnalytics()
    {
        $analytics = [
            'by_region' => Student::select('region_code', 'province_name', DB::raw('COUNT(*) as student_count'))
                ->whereNotNull('region_code')
                ->groupBy('region_code', 'province_name')
                ->orderByDesc('student_count')
                ->limit(10)
                ->get(),

            'top_subjects' => Subject::select('subjects.*', DB::raw('AVG(student_subject_scores.score) as avg_score'))
                ->join('student_subject_scores', 'subjects.id', '=', 'student_subject_scores.subject_id')
                ->whereNotNull('student_subject_scores.score')
                ->groupBy('subjects.id')
                ->orderByDesc('avg_score')
                ->limit(5)
                ->get(),

            'grade_overview' => StudentSubjectScore::select('grade_level', DB::raw('COUNT(*) as count'))
                ->whereNotNull('grade_level')
                ->groupBy('grade_level')
                ->get(),

            'foreign_language' => Student::select('ma_ngoai_ngu', DB::raw('COUNT(*) as count'))
                ->whereNotNull('ma_ngoai_ngu')
                ->groupBy('ma_ngoai_ngu')
                ->orderByDesc('count')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get subjects grouped by exam groups
     */
    private function getSubjectsByGroup(): array
    {
        return Subject::select('group_code', 'group_name', DB::raw('COUNT(*) as subject_count'))
            ->whereNotNull('group_code')
            ->groupBy('group_code', 'group_name')
            ->get()
            ->toArray();
    }

    /**
     * Get top students for any group - Generic endpoint
     */
    public function getTopStudentsByGroup(Request $request, $groupCode)
    {
        $validator = Validator::make(['group_code' => $groupCode], [
            'group_code' => 'required|string|in:A,B,C,D'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid group code'
            ], 422);
        }

        $groupSubjects = Subject::byGroup($groupCode)->get();

        if ($groupSubjects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy môn học cho khối này'
            ], 404);
        }

                $limit = $request->get('limit', 10);

        return response()->json([
            'success' => true,
            'data' => [
                'group_code' => $groupCode,
                'subjects' => $groupSubjects->pluck('display_name'),
                'message' => 'Feature under development for groups other than A'
            ]
        ]);
    }
}
