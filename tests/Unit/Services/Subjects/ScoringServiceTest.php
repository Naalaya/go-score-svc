<?php

namespace Tests\Unit\Services\Subjects;

use App\Models\Enums\GradeLevel;
use App\Services\Subjects\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    protected ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = new ScoringService();
    }

    public function test_get_grade_level_returns_correct_enum()
    {
        $this->assertEquals(GradeLevel::EXCELLENT, $this->scoringService->getGradeLevel(9.5));
        $this->assertEquals(GradeLevel::EXCELLENT, $this->scoringService->getGradeLevel(8.0));
        $this->assertEquals(GradeLevel::GOOD, $this->scoringService->getGradeLevel(7.5));
        $this->assertEquals(GradeLevel::GOOD, $this->scoringService->getGradeLevel(6.0));
        $this->assertEquals(GradeLevel::AVERAGE, $this->scoringService->getGradeLevel(5.0));
        $this->assertEquals(GradeLevel::AVERAGE, $this->scoringService->getGradeLevel(4.0));
        $this->assertEquals(GradeLevel::WEAK, $this->scoringService->getGradeLevel(3.5));
        $this->assertEquals(GradeLevel::WEAK, $this->scoringService->getGradeLevel(0.0));
    }

    public function test_validate_score_returns_correct_boolean()
    {
        $this->assertTrue($this->scoringService->validateScore(0.0));
        $this->assertTrue($this->scoringService->validateScore(5.5));
        $this->assertTrue($this->scoringService->validateScore(10.0));

        $this->assertFalse($this->scoringService->validateScore(-0.1));
        $this->assertFalse($this->scoringService->validateScore(10.1));
        $this->assertFalse($this->scoringService->validateScore(-5.0));
        $this->assertFalse($this->scoringService->validateScore(15.0));
    }

    public function test_calculate_statistics_returns_correct_structure()
    {
        $scores = [9.5, 8.0, 7.5, 6.0, 5.0, 4.0, 3.5, 2.0];
        $stats = $this->scoringService->calculateStatistics($scores);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('excellent', $stats);
        $this->assertArrayHasKey('good', $stats);
        $this->assertArrayHasKey('average', $stats);
        $this->assertArrayHasKey('weak', $stats);
        $this->assertArrayHasKey('average_score', $stats);

        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(2, $stats['excellent']); // 9.5, 8.0
        $this->assertEquals(2, $stats['good']);      // 7.5, 6.0
        $this->assertEquals(2, $stats['average']);   // 5.0, 4.0
        $this->assertEquals(2, $stats['weak']);      // 3.5, 2.0
    }

    public function test_calculate_statistics_with_empty_array()
    {
        $stats = $this->scoringService->calculateStatistics([]);

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['excellent']);
        $this->assertEquals(0, $stats['good']);
        $this->assertEquals(0, $stats['average']);
        $this->assertEquals(0, $stats['weak']);
    }

    public function test_calculate_statistics_computes_average_correctly()
    {
        $scores = [8.0, 6.0, 4.0, 2.0]; // Average should be 5.0
        $stats = $this->scoringService->calculateStatistics($scores);

        $this->assertEquals(5.0, $stats['average_score']);
    }
}
