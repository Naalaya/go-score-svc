<?php
namespace App\Console\Validation;

use App\Contracts\ScoringServiceInterface;
use App\Contracts\SubjectServiceInterface;

class ScoreValidationService
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
     * Validate an array of score data records.
     */
    public function validateScoreData(array $scoreData): array
    {
        $errors = [];
        $validatedData = [];

        foreach ($scoreData as $index => $studentScore) {
            $rowErrors = $this->validateSingleStudentScore($studentScore, $index);

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            $validatedData[] = $studentScore;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'validated_data' => $validatedData,
            'total_records' => count($scoreData),
            'valid_records' => count($validatedData),
            'error_records' => count($scoreData) - count($validatedData),
        ];
    }

    /**
     * Validate a single student score record.
     */
    private function validateSingleStudentScore(array $studentScore, int $index): array
    {
        $errors = [];

        // Validate SBD
        if (empty($studentScore['sbd']) || !preg_match('/^[0-9]{8,10}$/', $studentScore['sbd'])) {
            $errors["row_{$index}.sbd"] = 'SBD không hợp lệ (phải gồm 8-10 chữ số)';
        }

        // Validate scores for all subjects
        $hasValidScore = false;
        $subjects = $this->subjectService->getAllSubjects();

        foreach ($subjects as $subjectCode => $subjectData) {
            $score = $studentScore[$subjectCode] ?? null;

            if ($score !== null) {
                if (!$this->scoringService->validateScore($score)) {
                    $errors["row_{$index}.{$subjectCode}"] = "Điểm {$subjectData['display_name']} không hợp lệ (phải từ 0-10)";
                    continue;
                }
                $hasValidScore = true;
            }
        }

        if (!$hasValidScore) {
            $errors["row_{$index}"] = 'Phải có ít nhất 1 môn có điểm hợp lệ';
        }

        return $errors;
    }

    /**
     * Validate search parameters.
     */
    public function validateSearchParams(array $params): array
    {
        $errors = [];

        // Validate SBD
        if (empty($params['sbd'])) {
            $errors['sbd'] = 'Số báo danh là bắt buộc';
        } elseif (!preg_match('/^[0-9]{8,10}$/', $params['sbd'])) {
            $errors['sbd'] = 'Số báo danh phải gồm 8-10 chữ số';
        }

        // Validate year if provided
        if (isset($params['year'])) {
            if (!is_numeric($params['year']) || $params['year'] < 2020 || $params['year'] > 2025) {
                $errors['year'] = 'Năm phải từ 2020 đến 2025';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate group and subject codes for statistics.
     */
    public function validateStatisticsParams(array $params): array
    {
        $errors = [];

        // Validate group code if provided
        if (isset($params['group_code'])) {
            if (!$this->subjectService->validateGroupCode($params['group_code'])) {
                $errors['group_code'] = 'Mã khối không hợp lệ (phải là A, B, C hoặc D)';
            }
        }

        // Validate subject codes if provided
        if (isset($params['subject_codes']) && is_array($params['subject_codes'])) {
            foreach ($params['subject_codes'] as $index => $subjectCode) {
                if (!$this->subjectService->validateSubjectCode($subjectCode)) {
                    $errors["subject_codes.{$index}"] = "Mã môn học '{$subjectCode}' không hợp lệ";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
