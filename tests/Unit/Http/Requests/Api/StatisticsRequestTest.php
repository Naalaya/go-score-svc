<?php
namespace Tests\Unit\Http\Requests\Api;

use App\Http\Requests\Api\StatisticsRequest;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class StatisticsRequestTest extends TestCase
{
    public function test_authorize_returns_true()
    {
        $request = new StatisticsRequest();

        $this->assertTrue($request->authorize());
    }

    public function test_rules_method_returns_array()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $this->assertIsArray($rules);
    }

    public function test_group_code_validation_rules()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('group_code', $rules);

        $validGroupCodes = ['A', 'B', 'C', 'D'];
        foreach ($validGroupCodes as $code) {
            $validator = Validator::make(['group_code' => $code], $rules);
            $this->assertTrue($validator->passes(), "Group code {$code} should be valid");
        }
    }

    public function test_invalid_group_code_fails_validation()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $invalidGroupCodes = ['E', 'X', '1', 'a', 'AB'];
        foreach ($invalidGroupCodes as $code) {
            $validator = Validator::make(['group_code' => $code], $rules);
            $this->assertFalse($validator->passes(), "Group code {$code} should be invalid");
        }
    }

    public function test_subject_codes_validation_rules()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('subject_codes', $rules);
        $this->assertArrayHasKey('subject_codes.*', $rules);

        $validSubjectCodes = ['toan', 'ngu_van', 'ngoai_ngu', 'vat_li', 'hoa_hoc'];
        $validator = Validator::make(['subject_codes' => $validSubjectCodes], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_invalid_subject_codes_fail_validation()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $invalidSubjectCodes = ['invalid_subject', 'math', 'physics'];
        $validator = Validator::make(['subject_codes' => $invalidSubjectCodes], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_include_percentages_validation()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('include_percentages', $rules);

        // Test valid values
        $validValues = ['true', 'false', '1', '0'];
        foreach ($validValues as $value) {
            $validator = Validator::make(['include_percentages' => $value], $rules);
            $this->assertTrue($validator->passes(), "Value {$value} should be valid for include_percentages");
        }
    }

    public function test_format_validation_rules()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('format', $rules);

        $validFormats = ['json', 'csv'];
        foreach ($validFormats as $format) {
            $validator = Validator::make(['format' => $format], $rules);
            $this->assertTrue($validator->passes(), "Format {$format} should be valid");
        }

        $invalidFormats = ['xml', 'xlsx', 'pdf'];
        foreach ($invalidFormats as $format) {
            $validator = Validator::make(['format' => $format], $rules);
            $this->assertFalse($validator->passes(), "Format {$format} should be invalid");
        }
    }

    public function test_validation_data_method_converts_boolean_strings()
    {
        $request = new StatisticsRequest();
        $request->merge(['include_percentages' => 'true']);

        $validationData = $request->validationData();

        $this->assertTrue($validationData['include_percentages']);
    }

    public function test_validation_data_method_handles_false_string()
    {
        $request = new StatisticsRequest();
        $request->merge(['include_percentages' => 'false']);

        $validationData = $request->validationData();

        $this->assertFalse($validationData['include_percentages']);
    }

    public function test_validation_data_method_handles_numeric_strings()
    {
        $request = new StatisticsRequest();
        $request->merge(['include_percentages' => '1']);

        $validationData = $request->validationData();

        $this->assertTrue($validationData['include_percentages']);

        $request->merge(['include_percentages' => '0']);
        $validationData = $request->validationData();

        $this->assertFalse($validationData['include_percentages']);
    }

    public function test_messages_method_returns_vietnamese_messages()
    {
        $request = new StatisticsRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('group_code.in', $messages);
        $this->assertStringContainsString('Mã khối', $messages['group_code.in']);
    }

    public function test_combined_validation_with_all_parameters()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        $validData = [
            'group_code' => 'A',
            'subject_codes' => ['toan', 'vat_li', 'hoa_hoc'],
            'include_percentages' => 'true',
            'format' => 'json',
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_optional_parameters_are_truly_optional()
    {
        $request = new StatisticsRequest();
        $rules = $request->rules();

        // Empty request should be valid
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->passes());
    }
}
