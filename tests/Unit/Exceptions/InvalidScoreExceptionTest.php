<?php
namespace Tests\Unit\Exceptions;

use App\Exceptions\InvalidScoreException;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class InvalidScoreExceptionTest extends TestCase
{
    public function test_invalid_score_exception_message()
    {
        $score = 15.5;
        $exception = new InvalidScoreException($score);

        $this->assertEquals("Invalid score: {$score}. Must be 0-10.", $exception->getMessage());
    }

    public function test_invalid_score_exception_render()
    {
        $score = -2.5;
        $exception = new InvalidScoreException($score);

        $response = $exception->render();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('INVALID_SCORE', $data['error']);
        $this->assertEquals("Invalid score: {$score}. Must be 0-10.", $data['message']);
    }

    public function test_invalid_score_exception_with_zero()
    {
        $score = 0.0;
        $exception = new InvalidScoreException($score);

        $this->assertEquals("Invalid score: {$score}. Must be 0-10.", $exception->getMessage());
    }

    public function test_invalid_score_exception_with_negative_score()
    {
        $score = -1.0;
        $exception = new InvalidScoreException($score);

        $response = $exception->render();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid score: {$score}. Must be 0-10.", $data['message']);
    }

    public function test_invalid_score_exception_with_high_score()
    {
        $score = 15.75;
        $exception = new InvalidScoreException($score);

        $response = $exception->render();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Invalid score: {$score}. Must be 0-10.", $data['message']);
    }
}
