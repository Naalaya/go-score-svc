<?php

namespace Tests\Unit\Services\Subjects;

use App\Services\Subjects\SubjectService;
use Tests\TestCase;

class SubjectServiceTest extends TestCase
{
    protected SubjectService $subjectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subjectService = new SubjectService();
    }

    public function test_get_all_subjects_returns_array()
    {
        $subjects = $this->subjectService->getAllSubjects();

        $this->assertIsArray($subjects);
        $this->assertArrayHasKey('toan', $subjects);
        $this->assertArrayHasKey('ngu_van', $subjects);
    }

    public function test_get_subject_by_code_returns_correct_subject()
    {
        $subject = $this->subjectService->getSubjectByCode('toan');

        $this->assertIsArray($subject);
        $this->assertEquals('Toán', $subject['display_name']);
        $this->assertEquals('A', $subject['group_code']);
    }

    public function test_get_subject_by_invalid_code_returns_null()
    {
        $subject = $this->subjectService->getSubjectByCode('invalid');

        $this->assertNull($subject);
    }

    public function test_validate_subject_code_returns_correct_boolean()
    {
        $this->assertTrue($this->subjectService->validateSubjectCode('toan'));
        $this->assertTrue($this->subjectService->validateSubjectCode('vat_li'));
        $this->assertFalse($this->subjectService->validateSubjectCode('invalid'));
        $this->assertFalse($this->subjectService->validateSubjectCode(''));
    }

    public function test_validate_group_code_returns_correct_boolean()
    {
        $this->assertTrue($this->subjectService->validateGroupCode('A'));
        $this->assertTrue($this->subjectService->validateGroupCode('B'));
        $this->assertTrue($this->subjectService->validateGroupCode('C'));
        $this->assertTrue($this->subjectService->validateGroupCode('D'));
        $this->assertFalse($this->subjectService->validateGroupCode('E'));
        $this->assertFalse($this->subjectService->validateGroupCode(''));
    }

    public function test_get_subjects_by_group_returns_correct_subjects()
    {
        $subjectsGroupA = $this->subjectService->getSubjectsByGroup('A');

        $this->assertIsArray($subjectsGroupA);
        $this->assertCount(3, $subjectsGroupA);
        $this->assertArrayHasKey('toan', $subjectsGroupA);
        $this->assertArrayHasKey('vat_li', $subjectsGroupA);
        $this->assertArrayHasKey('hoa_hoc', $subjectsGroupA);

        $subjectsGroupC = $this->subjectService->getSubjectsByGroup('C');
        $this->assertCount(3, $subjectsGroupC);
        $this->assertArrayHasKey('ngu_van', $subjectsGroupC);
        $this->assertArrayHasKey('lich_su', $subjectsGroupC);
        $this->assertArrayHasKey('dia_li', $subjectsGroupC);
    }

    public function test_get_subjects_by_invalid_group_returns_empty_array()
    {
        $subjects = $this->subjectService->getSubjectsByGroup('X');

        $this->assertIsArray($subjects);
        $this->assertEmpty($subjects);
    }
}
