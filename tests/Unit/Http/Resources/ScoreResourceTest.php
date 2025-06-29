<?php
namespace Tests\Unit\Http\Resources;

use App\Http\Resources\ScoreResource;
use App\Models\Score;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class ScoreResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_resource_transforms_data_correctly()
    {
        $score = Score::factory()->create([
            'sbd' => '12345678',
            'toan' => 8.5,
            'ngu_van' => 7.0,
            'ngoai_ngu' => 6.5,
            'vat_li' => 9.0,
            'hoa_hoc' => 8.5,
            'sinh_hoc' => null,
            'lich_su' => 6.0,
            'dia_li' => 7.0,
            'gdcd' => 6.5,
        ]);

        $resource = new ScoreResource($score);
        $request = Request::create('/test');
        $array = $resource->toArray($request);

        $this->assertEquals($score->sbd, $array['sbd']);
        $this->assertEquals($score->toan, $array['toan']);
        $this->assertEquals($score->ngu_van, $array['ngu_van']);
        $this->assertEquals($score->ngoai_ngu, $array['ngoai_ngu']);
        $this->assertEquals($score->vat_li, $array['vat_li']);
        $this->assertEquals($score->hoa_hoc, $array['hoa_hoc']);
        $this->assertNull($array['sinh_hoc']);
        $this->assertEquals($score->lich_su, $array['lich_su']);
        $this->assertEquals($score->dia_li, $array['dia_li']);
        $this->assertEquals($score->gdcd, $array['gdcd']);
    }

    public function test_score_resource_handles_null_values()
    {
        $score = Score::factory()->create([
            'sbd' => '87654321',
            'toan' => null,
            'ngu_van' => null,
            'ngoai_ngu' => null,
            'vat_li' => null,
            'hoa_hoc' => null,
            'sinh_hoc' => null,
            'lich_su' => null,
            'dia_li' => null,
            'gdcd' => null,
        ]);

        $resource = new ScoreResource($score);
        $request = Request::create('/test');
        $array = $resource->toArray($request);

        $this->assertEquals($score->sbd, $array['sbd']);
        $this->assertNull($array['toan']);
        $this->assertNull($array['ngu_van']);
        $this->assertNull($array['ngoai_ngu']);
        $this->assertNull($array['vat_li']);
        $this->assertNull($array['hoa_hoc']);
        $this->assertNull($array['sinh_hoc']);
        $this->assertNull($array['lich_su']);
        $this->assertNull($array['dia_li']);
        $this->assertNull($array['gdcd']);
    }

    public function test_score_resource_json_serialization()
    {
        $score = Score::factory()->create([
            'sbd' => '11111111',
            'toan' => 10.0,
            'vat_li' => 9.5,
            'hoa_hoc' => 8.75,
        ]);

        $resource = new ScoreResource($score);
        $json = $resource->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals($score->sbd, $decoded['sbd']);
        $this->assertEquals($score->toan, $decoded['toan']);
        $this->assertEquals($score->vat_li, $decoded['vat_li']);
        $this->assertEquals($score->hoa_hoc, $decoded['hoa_hoc']);
    }

    public function test_score_resource_collection()
    {
        $scores = Score::factory()->count(3)->create();

        $collection = ScoreResource::collection($scores);
        $request = Request::create('/test');
        $array = $collection->toArray($request);

        $this->assertCount(3, $array);

        foreach ($array as $index => $scoreData) {
            $this->assertEquals($scores[$index]->sbd, $scoreData['sbd']);
            $this->assertIsArray($scoreData);
        }
    }

    public function test_score_resource_with_additional_data()
    {
        $score = Score::factory()->create(['sbd' => '99999999']);

        $resource = new ScoreResource($score);
        $resource->additional(['meta' => ['test' => true]]);

        $request = Request::create('/test');
        $response = $resource->toResponse($request);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
        $this->assertTrue($content['meta']['test']);
    }

    public function test_score_resource_preserves_data_types()
    {
        $score = Score::factory()->create([
            'sbd' => '22222222',
            'toan' => 8.25,
            'vat_li' => 7,    // Integer
            'hoa_hoc' => 9.0, // Float with .0
        ]);

        $resource = new ScoreResource($score);
        $request = Request::create('/test');
        $array = $resource->toArray($request);

        $this->assertIsNumeric($array['toan']);
        $this->assertEquals(8.25, (float)$array['toan']);
        $this->assertEquals(7.0, (float)$array['vat_li']);
        $this->assertEquals(9.0, (float)$array['hoa_hoc']);
    }
}
