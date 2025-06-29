<?php
namespace Tests\Feature\Api;

use App\Console\Validation\ScoreValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;
    public function test_search_score_validation_with_invalid_sbd()
    {
        $response = $this->postJson('/api/scores/search', ['sbd' => 'invalid']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['sbd']);
    }

    public function test_search_score_validation_with_valid_sbd()
    {
        $response = $this->postJson('/api/scores/search', ['sbd' => '12345678']);

        // Should pass validation but may return 404 if no data found
        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_search_score_validation_with_optional_parameters()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => '12345678',
            'year' => 2024,
            'include_statistics' => true,
            'include_metadata' => true,
        ]);

        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_search_score_validation_with_invalid_year()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => '12345678',
            'year' => 2030, // Invalid year
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['year']);
    }

    public function test_statistics_validation_with_valid_group_code()
    {
        $response = $this->getJson('/api/scores/statistics?group_code=A');

        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_statistics_validation_with_invalid_group_code()
    {
        $response = $this->getJson('/api/scores/statistics?group_code=INVALID');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_code']);
    }

    public function test_statistics_validation_with_subject_codes()
    {
        $response = $this->getJson('/api/scores/statistics?subject_codes[]=toan&subject_codes[]=vat_li');

        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_statistics_validation_with_invalid_subject_codes()
    {
        $response = $this->getJson('/api/scores/statistics?subject_codes[]=invalid_subject');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['subject_codes.0']);
    }

    public function test_score_validation_service_with_valid_data()
    {
        $validationService = app(ScoreValidationService::class);

        $scoreData = [
            [
                'sbd' => '12345678',
                'toan' => 8.5,
                'vat_li' => 7.0,
                'hoa_hoc' => 9.0,
            ]
        ];

        $result = $validationService->validateScoreData($scoreData);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(1, $result['total_records']);
        $this->assertEquals(1, $result['valid_records']);
    }

    public function test_score_validation_service_with_invalid_sbd()
    {
        $validationService = app(ScoreValidationService::class);

        $scoreData = [
            [
                'sbd' => 'invalid', // Invalid SBD
                'toan' => 8.5,
            ]
        ];

        $result = $validationService->validateScoreData($scoreData);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('row_0.sbd', $result['errors']);
        $this->assertEquals(0, $result['valid_records']);
    }

    public function test_score_validation_service_with_invalid_scores()
    {
        $validationService = app(ScoreValidationService::class);

        $scoreData = [
            [
                'sbd' => '12345678',
                'toan' => 15.0, // Invalid score > 10
                'vat_li' => -1.0, // Invalid score < 0
            ]
        ];

        $result = $validationService->validateScoreData($scoreData);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('row_0.toan', $result['errors']);
        $this->assertArrayHasKey('row_0.vat_li', $result['errors']);
    }

    public function test_score_validation_service_with_no_valid_scores()
    {
        $validationService = app(ScoreValidationService::class);

        $scoreData = [
            [
                'sbd' => '12345678',
                // No scores provided
            ]
        ];

        $result = $validationService->validateScoreData($scoreData);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('row_0', $result['errors']);
    }

    public function test_search_params_validation()
    {
        $validationService = app(ScoreValidationService::class);

        // Valid params
        $validResult = $validationService->validateSearchParams(['sbd' => '12345678']);
        $this->assertTrue($validResult['valid']);

        // Invalid params
        $invalidResult = $validationService->validateSearchParams(['sbd' => 'invalid']);
        $this->assertFalse($invalidResult['valid']);
        $this->assertArrayHasKey('sbd', $invalidResult['errors']);
    }

    public function test_statistics_params_validation()
    {
        $validationService = app(ScoreValidationService::class);

        // Valid params
        $validResult = $validationService->validateStatisticsParams(['group_code' => 'A']);
        $this->assertTrue($validResult['valid']);

        // Invalid group code
        $invalidResult = $validationService->validateStatisticsParams(['group_code' => 'X']);
        $this->assertFalse($invalidResult['valid']);
        $this->assertArrayHasKey('group_code', $invalidResult['errors']);
    }
}
