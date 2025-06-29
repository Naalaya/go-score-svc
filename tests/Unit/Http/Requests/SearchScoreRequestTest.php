<?php
namespace Tests\Unit\Http\Requests;

use App\Http\Requests\SearchScoreRequest;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class SearchScoreRequestTest extends TestCase
{
    public function test_authorize_returns_true()
    {
        $request = new SearchScoreRequest();

        $this->assertTrue($request->authorize());
    }

    public function test_rules_method_returns_array()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('sbd', $rules);
    }

    public function test_sbd_validation_rules()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $sbdRules = $rules['sbd'];
        $this->assertStringContainsString('required', $sbdRules);
        $this->assertStringContainsString('string', $sbdRules);
        $this->assertStringContainsString('regex', $sbdRules);
    }

    public function test_valid_sbd_passes_validation()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $validSbds = ['12345678', '87654321', '11111111', '99999999'];

        foreach ($validSbds as $sbd) {
            $validator = Validator::make(['sbd' => $sbd], $rules);
            $this->assertTrue($validator->passes(), "SBD {$sbd} should be valid");
        }
    }

        public function test_invalid_sbd_fails_validation()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $invalidSbds = [
            'abc12345',    // Contains letters
            '1234567',     // Too short
            '1234-5678',   // Contains dash
            '',            // Empty
            '12345678a',   // Contains letter at end
        ];

        foreach ($invalidSbds as $sbd) {
            $validator = Validator::make(['sbd' => $sbd], $rules);
            $this->assertFalse($validator->passes(), "SBD {$sbd} should be invalid");
        }
    }

    public function test_year_validation_rules()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('year', $rules);
        $yearRules = $rules['year'];
        $this->assertStringContainsString('sometimes', $yearRules);
        $this->assertStringContainsString('integer', $yearRules);
        $this->assertStringContainsString('between', $yearRules);
    }

    public function test_valid_year_passes_validation()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $validYears = [2020, 2021, 2022, 2023, 2024, 2025];

        foreach ($validYears as $year) {
            $validator = Validator::make(['sbd' => '12345678', 'year' => $year], $rules);
            $this->assertTrue($validator->passes(), "Year {$year} should be valid");
        }
    }

    public function test_invalid_year_fails_validation()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $invalidYears = [2019, 2026, 2030, 1999, 'abc', 2020.5];

        foreach ($invalidYears as $year) {
            $validator = Validator::make(['sbd' => '12345678', 'year' => $year], $rules);
            $this->assertFalse($validator->passes(), "Year {$year} should be invalid");
        }
    }

    public function test_optional_parameters_validation()
    {
        $request = new SearchScoreRequest();
        $rules = $request->rules();

        $validData = [
            'sbd' => '12345678',
            'include_statistics' => true,
            'include_metadata' => false,
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_messages_method_returns_array()
    {
        $request = new SearchScoreRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('sbd.required', $messages);
        $this->assertArrayHasKey('sbd.regex', $messages);
    }

    public function test_custom_error_messages_are_vietnamese()
    {
        $request = new SearchScoreRequest();
        $messages = $request->messages();

        $this->assertStringContainsString('Số báo danh', $messages['sbd.required']);
        $this->assertStringContainsString('chữ số', $messages['sbd.regex']);
    }

    public function test_get_sbd_method()
    {
        $request = new SearchScoreRequest();
        $request->merge(['sbd' => '12345678']);

        // Mock validation
        $request->setValidator(Validator::make(['sbd' => '12345678'], $request->rules()));

        $this->assertEquals('12345678', $request->getSbd());
    }

        public function test_get_year_method()
    {
        $request = new SearchScoreRequest();
        $request->merge(['sbd' => '12345678', 'year' => 2024]);

        $request->setValidator(Validator::make(['sbd' => '12345678', 'year' => 2024], $request->rules()));

        $this->assertEquals(2024, $request->getYear());
    }

    public function test_get_year_method_returns_null_when_not_provided()
    {
        $request = new SearchScoreRequest();
        $request->merge(['sbd' => '12345678']);

        $request->setValidator(Validator::make(['sbd' => '12345678'], $request->rules()));

        $this->assertNull($request->getYear());
    }

        public function test_should_include_statistics_method()
    {
        $request = new SearchScoreRequest();
        $request->merge(['sbd' => '12345678', 'include_statistics' => true]);

        $request->setValidator(Validator::make(['sbd' => '12345678', 'include_statistics' => true], $request->rules()));

        $this->assertTrue($request->shouldIncludeStatistics());
    }

        public function test_should_include_metadata_method()
    {
        $request = new SearchScoreRequest();
        $request->merge(['sbd' => '12345678', 'include_metadata' => true]);

        $request->setValidator(Validator::make(['sbd' => '12345678', 'include_metadata' => true], $request->rules()));

        $this->assertTrue($request->shouldIncludeMetadata());
    }
}
