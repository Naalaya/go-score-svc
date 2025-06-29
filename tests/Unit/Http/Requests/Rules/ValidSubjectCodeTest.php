<?php
namespace Tests\Unit\Http\Requests\Rules;

use App\Http\Requests\Rules\ValidSubjectCode;
use Tests\TestCase;

class ValidSubjectCodeTest extends TestCase
{
    protected ValidSubjectCode $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new ValidSubjectCode();
    }

    public function test_validates_valid_subject_codes()
    {
        $validCodes = ['toan', 'ngu_van', 'ngoai_ngu', 'vat_li', 'hoa_hoc', 'sinh_hoc', 'lich_su', 'dia_li', 'gdcd'];

        foreach ($validCodes as $code) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('subject_code', $code, $fail);

            $this->assertFalse($failCalled, "Subject code {$code} should be valid");
        }
    }

    public function test_rejects_invalid_subject_codes()
    {
        $invalidCodes = ['invalid', 'xyz', '', 'math', 'physics'];

        foreach ($invalidCodes as $code) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('subject_code', $code, $fail);

            $this->assertTrue($failCalled, "Subject code {$code} should be invalid");
        }
    }
}
