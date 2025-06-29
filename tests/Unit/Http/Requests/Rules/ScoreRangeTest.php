<?php
namespace Tests\Unit\Http\Requests\Rules;

use App\Http\Requests\Rules\ScoreRange;
use Tests\TestCase;

class ScoreRangeTest extends TestCase
{
    protected ScoreRange $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new ScoreRange();
    }

    public function test_validates_null_score()
    {
        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $this->rule->validate('score', null, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_validates_valid_scores()
    {
        $validScores = [0, 0.0, 5.5, 10, 10.0, 8.75];

        foreach ($validScores as $score) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('score', $score, $fail);

            $this->assertFalse($failCalled, "Score {$score} should be valid");
        }
    }

    public function test_rejects_invalid_scores()
    {
        $invalidScores = [-0.1, -1, 10.1, 15, 'abc', [], true];

        foreach ($invalidScores as $score) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('score', $score, $fail);

            $this->assertTrue($failCalled, "Score " . (is_array($score) ? 'array' : (is_bool($score) ? ($score ? 'true' : 'false') : $score)) . " should be invalid");
        }
    }
}
