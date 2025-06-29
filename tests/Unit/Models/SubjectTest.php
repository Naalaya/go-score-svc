<?php
namespace Tests\Unit\Models;

use App\Models\Subject;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test subjects using the configuration
        $subjects = config('subjects.subjects');
        foreach ($subjects as $code => $data) {
            Subject::create([
                'code' => $code,
                'display_name' => $data['display_name'],
                'group_code' => $data['group_code'],
                'order' => $data['order'],
            ]);
        }
    }

    public function test_subject_creation()
    {
        $subject = Subject::create([
            'code' => 'test_subject',
            'display_name' => 'Test Subject',
            'group_code' => 'A',
            'order' => 99,
        ]);

        $this->assertInstanceOf(Subject::class, $subject);
        $this->assertEquals('test_subject', $subject->code);
        $this->assertEquals('Test Subject', $subject->display_name);
        $this->assertEquals('A', $subject->group_code);
    }

    public function test_active_scope()
    {
        $activeSubjects = Subject::active()->get();

        $this->assertGreaterThan(0, $activeSubjects->count());
        foreach ($activeSubjects as $subject) {
            $this->assertTrue($subject->is_active);
        }
    }

    public function test_ordered_scope()
    {
        $orderedSubjects = Subject::ordered()->get();

        $this->assertGreaterThan(0, $orderedSubjects->count());

        // Check if they are in ascending order
        $previousOrder = 0;
        foreach ($orderedSubjects as $subject) {
            $this->assertGreaterThanOrEqual($previousOrder, $subject->order);
            $previousOrder = $subject->order;
        }
    }

    public function test_by_group_scope()
    {
        $groupASubjects = Subject::byGroup('A')->get();

        foreach ($groupASubjects as $subject) {
            $this->assertEquals('A', $subject->group_code);
        }

        // Should have toan, vat_li, hoa_hoc
        $codes = $groupASubjects->pluck('code')->toArray();
        $this->assertContains('toan', $codes);
        $this->assertContains('vat_li', $codes);
        $this->assertContains('hoa_hoc', $codes);
    }

    public function test_by_group_scope_with_invalid_group()
    {
        $invalidGroupSubjects = Subject::byGroup('X')->get();

        $this->assertEquals(0, $invalidGroupSubjects->count());
    }

        public function test_required_subjects_from_config()
    {
        $subjects = config('subjects.subjects');
        $requiredSubjects = collect($subjects)->filter(fn($subject) => $subject['is_required'])->keys();

        // Should include toan, ngu_van, ngoai_ngu
        $this->assertContains('toan', $requiredSubjects);
        $this->assertContains('ngu_van', $requiredSubjects);
        $this->assertContains('ngoai_ngu', $requiredSubjects);
    }

    public function test_get_grade_level_by_score_method()
    {
        $this->assertEquals('excellent', Subject::getGradeLevelByScore(9.5));
        $this->assertEquals('excellent', Subject::getGradeLevelByScore(8.0));
        $this->assertEquals('good', Subject::getGradeLevelByScore(7.5));
        $this->assertEquals('good', Subject::getGradeLevelByScore(6.0));
        $this->assertEquals('average', Subject::getGradeLevelByScore(5.0));
        $this->assertEquals('average', Subject::getGradeLevelByScore(4.0));
        $this->assertEquals('weak', Subject::getGradeLevelByScore(3.5));
        $this->assertEquals('weak', Subject::getGradeLevelByScore(0.0));
        $this->assertEquals('N/A', Subject::getGradeLevelByScore(null));
    }

    public function test_get_grade_level_label_by_score_method()
    {
        $this->assertEquals('Giỏi', Subject::getGradeLevelLabelByScore(9.5));
        $this->assertEquals('Khá', Subject::getGradeLevelLabelByScore(7.5));
        $this->assertEquals('Trung bình', Subject::getGradeLevelLabelByScore(5.0));
        $this->assertEquals('Yếu', Subject::getGradeLevelLabelByScore(3.5));
        $this->assertEquals('N/A', Subject::getGradeLevelLabelByScore(null));
    }

        public function test_get_grade_levels_method()
    {
        $gradeLevels = Subject::getGradeLevels();

        $this->assertIsArray($gradeLevels);
        $this->assertArrayHasKey('excellent', $gradeLevels);
        $this->assertArrayHasKey('good', $gradeLevels);
        $this->assertArrayHasKey('average', $gradeLevels);
        $this->assertArrayHasKey('weak', $gradeLevels);

        $this->assertEquals('Giỏi', $gradeLevels['excellent']['label']);
        // Test actual structure returned by the method
        $this->assertIsArray($gradeLevels['excellent']);
    }

        public function test_subject_display_name()
    {
        $subject = Subject::where('code', 'toan')->first();

        $this->assertEquals('Toán', $subject->display_name);
    }

    public function test_subject_relationships_setup()
    {
        $subject = Subject::where('code', 'toan')->first();

        // Test that the model is set up for relationships
        $this->assertTrue(method_exists($subject, 'studentSubjectScores'));
    }

    public function test_subject_mass_assignment()
    {
        $data = [
            'code' => 'new_subject',
            'display_name' => 'New Subject',
            'group_code' => 'B',
            'order' => 10,
        ];

        $subject = Subject::create($data);

                foreach (['code', 'display_name', 'group_code', 'order'] as $field) {
            $this->assertEquals($data[$field], $subject->$field);
        }

        // Test that subject was created successfully
        $this->assertNotNull($subject->id);
    }
}
