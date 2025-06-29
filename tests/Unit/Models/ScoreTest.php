<?php
namespace Tests\Unit\Models;

use App\Models\Score;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_factory_creates_valid_score()
    {
        $score = Score::factory()->create();

        $this->assertNotNull($score->sbd);
        $this->assertIsString($score->sbd);
        $this->assertEquals(8, strlen($score->sbd));
    }

    public function test_score_factory_with_custom_sbd()
    {
        $customSbd = '12345678';
        $score = Score::factory()->withSbd($customSbd)->create();

        $this->assertEquals($customSbd, $score->sbd);
    }

    public function test_score_factory_high_score_group_a()
    {
        $score = Score::factory()->highScoreGroupA()->create();

        $this->assertGreaterThanOrEqual(8.0, $score->toan);
        $this->assertGreaterThanOrEqual(8.0, $score->vat_li);
        $this->assertGreaterThanOrEqual(8.0, $score->hoa_hoc);
        $this->assertLessThanOrEqual(10.0, $score->toan);
        $this->assertLessThanOrEqual(10.0, $score->vat_li);
        $this->assertLessThanOrEqual(10.0, $score->hoa_hoc);
    }

    public function test_score_attributes_are_fillable()
    {
        $data = [
            'sbd' => '12345678',
            'toan' => 8.5,
            'ngu_van' => 7.0,
            'ngoai_ngu' => 6.5,
            'vat_li' => 9.0,
            'hoa_hoc' => 8.5,
            'sinh_hoc' => 7.5,
            'lich_su' => 6.0,
            'dia_li' => 7.0,
            'gdcd' => 6.5,
        ];

        $score = Score::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $score->$key);
        }
    }

    public function test_score_casts_numeric_fields_properly()
    {
        $score = Score::factory()->create([
            'toan' => '8.5',
            'vat_li' => '7.25',
        ]);

        $this->assertIsNumeric($score->toan);
        $this->assertIsNumeric($score->vat_li);
        $this->assertEquals(8.5, (float)$score->toan);
        $this->assertEquals(7.25, (float)$score->vat_li);
    }

    public function test_score_handles_null_values()
    {
        $score = Score::factory()->create([
            'toan' => null,
            'vat_li' => 8.0,
            'hoa_hoc' => null,
        ]);

        $this->assertNull($score->toan);
        $this->assertNull($score->hoa_hoc);
        $this->assertEquals(8.0, $score->vat_li);
    }

    public function test_score_find_by_sbd()
    {
        $sbd = '12345678';
        Score::factory()->withSbd($sbd)->create();

        $foundScore = Score::where('sbd', $sbd)->first();

        $this->assertNotNull($foundScore);
        $this->assertEquals($sbd, $foundScore->sbd);
    }

    public function test_score_unique_sbd_constraint()
    {
        $sbd = '12345678';
        Score::factory()->withSbd($sbd)->create();

        $this->expectException(\Illuminate\Database\QueryException::class);
        Score::factory()->withSbd($sbd)->create();
    }
}
