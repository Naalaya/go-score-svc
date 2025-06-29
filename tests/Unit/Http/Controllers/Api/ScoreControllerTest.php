<?php
namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\ScoreController;
use App\Http\Requests\SearchScoreRequest;
use App\Http\Requests\Api\StatisticsRequest;
use App\Http\Resources\ScoreResource;
use App\Contracts\ScoreServiceInterface;
use App\Models\Score;
use Tests\TestCase;
use Mockery;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoreControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $mockScoreService;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockScoreService = Mockery::mock(ScoreServiceInterface::class);
        $this->controller = new ScoreController($this->mockScoreService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

                public function test_search_by_student_id_returns_score_when_found()
    {
        $sbd = '12345678';
        $mockScore = Mockery::mock(Score::class);
        $mockScore->shouldReceive('toArray')->andReturn([
            'sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
            'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
            'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5
        ]);
        $mockScore->shouldReceive('getAttribute')->andReturnUsing(function($key) use ($sbd) {
            $data = ['sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
                     'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
                     'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5];
            return $data[$key] ?? null;
        });

        $mockRequest = Mockery::mock(SearchScoreRequest::class);
        $mockRequest->shouldReceive('getSbd')->andReturn($sbd);
        $mockRequest->shouldReceive('shouldIncludeStatistics')->andReturn(false);
        $mockRequest->shouldReceive('shouldIncludeMetadata')->andReturn(false);

        $this->mockScoreService
            ->shouldReceive('findByStudentId')
            ->with($sbd)
            ->andReturn($mockScore);

        $response = $this->controller->searchByStudentId($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_search_by_student_id_returns_404_when_not_found()
    {
        $sbd = '99999999';

        $mockRequest = Mockery::mock(SearchScoreRequest::class);
        $mockRequest->shouldReceive('getSbd')->andReturn($sbd);

        $this->mockScoreService
            ->shouldReceive('findByStudentId')
            ->with($sbd)
            ->andReturn(null);

        $response = $this->controller->searchByStudentId($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Không tìm thấy số báo danh này', $data['message']);
    }

            public function test_search_by_student_id_with_statistics()
    {
        $sbd = '12345678';
        $mockScore = Mockery::mock(Score::class);
        $mockScore->shouldReceive('toArray')->andReturn([
            'sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
            'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
            'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5
        ]);
        $mockScore->shouldReceive('getAttribute')->andReturnUsing(function($key) use ($sbd) {
            $data = ['sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
                     'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
                     'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5];
            return $data[$key] ?? null;
        });
        $mockStatistics = ['statistics' => ['total' => 100]];

        $mockRequest = Mockery::mock(SearchScoreRequest::class);
        $mockRequest->shouldReceive('getSbd')->andReturn($sbd);
        $mockRequest->shouldReceive('shouldIncludeStatistics')->andReturn(true);
        $mockRequest->shouldReceive('shouldIncludeMetadata')->andReturn(false);

        $this->mockScoreService
            ->shouldReceive('findByStudentId')
            ->with($sbd)
            ->andReturn($mockScore);

        $this->mockScoreService
            ->shouldReceive('getStatisticsReport')
            ->andReturn($mockStatistics);

        $response = $this->controller->searchByStudentId($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

                public function test_search_by_student_id_with_metadata()
    {
        $sbd = '12345678';
        $year = 2024;
        $mockScore = Mockery::mock(Score::class);
        $mockScore->shouldReceive('toArray')->andReturn([
            'sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
            'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
            'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5
        ]);
        $mockScore->shouldReceive('getAttribute')->andReturnUsing(function($key) use ($sbd) {
            $data = ['sbd' => $sbd, 'toan' => 8.5, 'ngu_van' => 7.0, 'ngoai_ngu' => 6.5,
                     'vat_li' => 9.0, 'hoa_hoc' => 8.5, 'sinh_hoc' => 7.5,
                     'lich_su' => 6.0, 'dia_li' => 7.0, 'gdcd' => 6.5];
            return $data[$key] ?? null;
        });

        $mockRequest = Mockery::mock(SearchScoreRequest::class);
        $mockRequest->shouldReceive('getSbd')->andReturn($sbd);
        $mockRequest->shouldReceive('shouldIncludeStatistics')->andReturn(false);
        $mockRequest->shouldReceive('shouldIncludeMetadata')->andReturn(true);
        $mockRequest->shouldReceive('getYear')->andReturn($year);

        $this->mockScoreService
            ->shouldReceive('findByStudentId')
            ->with($sbd)
            ->andReturn($mockScore);

        $response = $this->controller->searchByStudentId($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_get_statistics_report()
    {
        $mockStatistics = [
            'statistics' => [
                ['subject' => 'toan', 'total' => 100],
                ['subject' => 'vat_li', 'total' => 95]
            ],
            'summary' => ['total_students' => 1000]
        ];

        $mockRequest = Mockery::mock(StatisticsRequest::class);
        $mockRequest->shouldReceive('has')->with('group_code')->andReturn(false);
        $mockRequest->shouldReceive('has')->with('subject_codes')->andReturn(false);
        $mockRequest->shouldReceive('input')->with('include_percentages', false)->andReturn(false);

        $this->mockScoreService
            ->shouldReceive('getStatisticsReport')
            ->andReturn($mockStatistics);

        $response = $this->controller->getStatisticsReport($mockRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('2.0', $data['api_version']);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_get_statistics_report_with_group_filter()
    {
        $mockStatistics = ['statistics' => [], 'summary' => []];

        $mockRequest = Mockery::mock(StatisticsRequest::class);
        $mockRequest->shouldReceive('has')->with('group_code')->andReturn(true);
        $mockRequest->shouldReceive('has')->with('subject_codes')->andReturn(false);
        $mockRequest->shouldReceive('input')->with('group_code')->andReturn('A');
        $mockRequest->shouldReceive('input')->with('include_percentages', false)->andReturn(false);

        $this->mockScoreService
            ->shouldReceive('getStatisticsReport')
            ->andReturn($mockStatistics);

        $response = $this->controller->getStatisticsReport($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('A', $data['data']['filtered_by_group']);
    }

    public function test_get_statistics_report_with_percentages()
    {
        $mockStatistics = [
            'statistics' => [
                ['total' => 100, 'excellent' => 20, 'good' => 30, 'average' => 40, 'weak' => 10],
                ['total' => 50, 'excellent' => 10, 'good' => 15, 'average' => 20, 'weak' => 5]
            ],
            'summary' => []
        ];

        $mockRequest = Mockery::mock(StatisticsRequest::class);
        $mockRequest->shouldReceive('has')->with('group_code')->andReturn(false);
        $mockRequest->shouldReceive('has')->with('subject_codes')->andReturn(false);
        $mockRequest->shouldReceive('input')->with('include_percentages', false)->andReturn(true);

        $this->mockScoreService
            ->shouldReceive('getStatisticsReport')
            ->andReturn($mockStatistics);

        $response = $this->controller->getStatisticsReport($mockRequest);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $statistics = $data['data']['statistics'];

        // Check that percentages were calculated
        $this->assertEquals(20.0, $statistics[0]['percentages']['excellent']);
        $this->assertEquals(30.0, $statistics[0]['percentages']['good']);
        $this->assertEquals(40.0, $statistics[0]['percentages']['average']);
        $this->assertEquals(10.0, $statistics[0]['percentages']['weak']);
    }

    public function test_get_top10_group_a()
    {
        $mockTop10 = [
            'top_students' => [
                ['rank' => 1, 'sbd' => '12345678', 'total_score' => 28.5],
                ['rank' => 2, 'sbd' => '87654321', 'total_score' => 27.0]
            ],
            'group_name' => 'Khối A',
            'subjects' => ['Toán', 'Vật lý', 'Hóa học']
        ];

        $this->mockScoreService
            ->shouldReceive('getTop10GroupA')
            ->andReturn($mockTop10);

        $response = $this->controller->getTop10GroupA();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Khối A', $data['data']['group_name']);
        $this->assertCount(2, $data['data']['top_students']);
    }
}
