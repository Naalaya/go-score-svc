<?php
namespace Tests\Unit\Exceptions;

use App\Exceptions\InvalidSubjectException;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class InvalidSubjectExceptionTest extends TestCase
{
    public function test_invalid_subject_exception_message()
    {
        $subjectCode = 'invalid_subject';
        $exception = new InvalidSubjectException($subjectCode);

        $this->assertEquals("Invalid subject code: {$subjectCode}", $exception->getMessage());
    }

    public function test_invalid_subject_exception_render()
    {
        $subjectCode = 'xyz';
        $exception = new InvalidSubjectException($subjectCode);

        $response = $exception->render();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('INVALID_SUBJECT', $data['error']);
        $this->assertEquals("Invalid subject code: {$subjectCode}", $data['message']);
    }

    public function test_invalid_subject_exception_with_empty_code()
    {
        $exception = new InvalidSubjectException('');

        $this->assertEquals("Invalid subject code: ", $exception->getMessage());

        $response = $exception->render();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Invalid subject code: ", $data['message']);
    }

    public function test_invalid_subject_exception_with_special_characters()
    {
        $subjectCode = 'test@#$%';
        $exception = new InvalidSubjectException($subjectCode);

        $this->assertEquals("Invalid subject code: {$subjectCode}", $exception->getMessage());

        $response = $exception->render();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Invalid subject code: {$subjectCode}", $data['message']);
    }
}
