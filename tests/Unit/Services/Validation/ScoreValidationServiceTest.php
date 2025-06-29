<?php
namespace Tests\Unit\Services\Validation;

use App\Console\Validation\ScoreValidationService;
use App\Services\Subjects\ScoringService;
use App\Services\Subjects\SubjectService;
use Tests\TestCase;

class ScoreValidationServiceTest extends TestCase
{
    protected ScoreValidationService $validationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationService = new ScoreValidationService(
            new SubjectService(),
            new ScoringService()
        );
    }

    public function test_validates_multiple_student_records()
    {
        $scoreData = [
            [
                'sbd' => '12345678',
                'toan' => 8.5,
                'vat_li' => 7.0,
                'hoa_hoc' => 9.0,
            ],
            [
                'sbd' => '87654321',
                'ngu_van' => 6.5,
                'lich_su' => 8.0,
            ],
        ];

        $result = $this->validationService->validateScoreData($scoreData);

        $this->assertTrue($result['valid']);
        $this->assertEquals(2, $result['total_records']);
        $this->assertEquals(2, $result['valid_records']);
        $this->assertEquals(0, $result['error_records']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validates_mixed_valid_invalid_records()
    {
        $scoreData = [
            // Valid record
            [
                'sbd' => '12345678',
                'toan' => 8.5,
            ],
            // Invalid SBD
            [
                'sbd' => 'abc',
                'toan' => 7.0,
            ],
            // Invalid score
            [
                'sbd' => '87654321',
                'toan' => 15.0, // > 10
            ],
            // No scores
            [
                'sbd' => '11111111',
            ],
        ];

        $result = $this->validationService->validateScoreData($scoreData);

        $this->assertFalse($result['valid']);
        $this->assertEquals(4, $result['total_records']);
        $this->assertEquals(1, $result['valid_records']);
        $this->assertEquals(3, $result['error_records']);
        $this->assertCount(4, $result['errors']); // Should have multiple error entries
    }

    public function test_validates_search_params_comprehensive()
    {
        // Valid params
        $validParams = [
            'sbd' => '12345678',
            'year' => 2024,
        ];
        $result = $this->validationService->validateSearchParams($validParams);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        // Invalid SBD
        $invalidSbd = ['sbd' => 'abc123'];
        $result = $this->validationService->validateSearchParams($invalidSbd);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('sbd', $result['errors']);

        // Invalid year
        $invalidYear = ['sbd' => '12345678', 'year' => 2030];
        $result = $this->validationService->validateSearchParams($invalidYear);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('year', $result['errors']);

        // Missing SBD
        $missingSbd = ['year' => 2024];
        $result = $this->validationService->validateSearchParams($missingSbd);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('sbd', $result['errors']);
    }

    public function test_validates_statistics_params_comprehensive()
    {
        // Valid group code
        $validGroup = ['group_code' => 'A'];
        $result = $this->validationService->validateStatisticsParams($validGroup);
        $this->assertTrue($result['valid']);

        // Valid subject codes
        $validSubjects = ['subject_codes' => ['toan', 'vat_li', 'hoa_hoc']];
        $result = $this->validationService->validateStatisticsParams($validSubjects);
        $this->assertTrue($result['valid']);

        // Invalid group code
        $invalidGroup = ['group_code' => 'X'];
        $result = $this->validationService->validateStatisticsParams($invalidGroup);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('group_code', $result['errors']);

        // Invalid subject codes
        $invalidSubjects = ['subject_codes' => ['invalid_subject', 'toan']];
        $result = $this->validationService->validateStatisticsParams($invalidSubjects);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('subject_codes.0', $result['errors']);

        // Mixed valid/invalid subject codes
        $mixedSubjects = ['subject_codes' => ['toan', 'invalid', 'vat_li']];
        $result = $this->validationService->validateStatisticsParams($mixedSubjects);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('subject_codes.1', $result['errors']);
    }

    public function test_handles_edge_cases()
    {
        // Empty score data
        $result = $this->validationService->validateScoreData([]);
        $this->assertTrue($result['valid']);
        $this->assertEquals(0, $result['total_records']);

        // Score data with null values
        $scoreData = [
            [
                'sbd' => '12345678',
                'toan' => null,
                'vat_li' => 7.5,
            ]
        ];
        $result = $this->validationService->validateScoreData($scoreData);
        $this->assertTrue($result['valid']); // null scores are allowed

        // Score data with edge scores (0 and 10)
        $edgeScoreData = [
            [
                'sbd' => '12345678',
                'toan' => 0.0,
                'vat_li' => 10.0,
            ]
        ];
        $result = $this->validationService->validateScoreData($edgeScoreData);
        $this->assertTrue($result['valid']);
    }
}
