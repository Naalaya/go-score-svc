<?php
namespace Tests\Feature\Api;

use App\Models\Score;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        Score::factory()->withSbd('12345678')->create([
            'toan' => 8.5,
            'vat_li' => 7.5,
            'hoa_hoc' => 9.0,
            'ngu_van' => 6.5,
        ]);

        Score::factory()->highScoreGroupA()->count(10)->create();
        Score::factory()->count(50)->create(); // General test data
    }

    public function test_search_score_by_valid_sbd()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => '12345678'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'sbd',
                         'toan',
                         'vat_li',
                         'hoa_hoc',
                         'ngu_van'
                     ]
                 ])
                 ->assertJson([
                     'success' => true
                 ]);
    }

    public function test_search_score_with_enhanced_parameters()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => '12345678',
            'include_statistics' => true,
            'include_metadata' => true,
            'year' => 2024
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ]);
    }

    public function test_search_score_with_invalid_sbd()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => 'invalid'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['sbd']);
    }

    public function test_search_score_not_found()
    {
        $response = $this->postJson('/api/scores/search', [
            'sbd' => '99999999'
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Không tìm thấy số báo danh này'
                 ]);
    }

    public function test_get_statistics_report()
    {
        $response = $this->getJson('/api/scores/statistics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'statistics',
                         'summary'
                     ],
                     'api_version'
                 ])
                 ->assertJson([
                     'success' => true,
                     'api_version' => '2.0'
                 ]);
    }

    public function test_get_statistics_with_filters()
    {
        $response = $this->getJson('/api/scores/statistics?group_code=A&include_percentages=true');

        $response->assertStatus(200)
                 ->assertJsonPath('data.filtered_by_group', 'A')
                 ->assertJsonPath('success', true);
    }

    public function test_get_statistics_with_invalid_group()
    {
        $response = $this->getJson('/api/scores/statistics?group_code=INVALID');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['group_code']);
    }

    public function test_get_top10_group_a()
    {
        $response = $this->getJson('/api/scores/top10-group-a');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'top_students',
                         'group_name',
                         'subjects'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'group_name' => 'Khối A',
                         'subjects' => ['Toán', 'Vật lý', 'Hóa học']
                     ]
                 ]);
    }

    public function test_get_subjects_endpoint()
    {
        $response = $this->getJson('/api/subjects');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ])
                 ->assertJson([
                     'success' => true
                 ]);
    }
}
