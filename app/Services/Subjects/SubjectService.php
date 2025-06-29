<?php
namespace App\Services\Subjects;
use App\Contracts\SubjectServiceInterface;

class SubjectService implements SubjectServiceInterface
{
    protected array $subjects;
    protected array $groups;

    public function __construct()
    {
        $this->subjects = config('subjects.subjects', []);
        $this->groups = config('subjects.groups', []);
    }

    public function getAllSubjects(): array
    {
        return $this->subjects;
    }

    public function getSubjectByCode(string $code): ?array
    {
        return $this->subjects[$code] ?? null;
    }

    public function getSubjectsByGroup(string $groupCode): array
    {
        if (!$this->validateGroupCode($groupCode)) {
            return [];
        }

        $groupSubjects = $this->groups[$groupCode]['subjects'] ?? [];
        $result = [];

        foreach ($groupSubjects as $subjectCode) {
            if (isset($this->subjects[$subjectCode])) {
                $result[$subjectCode] = $this->subjects[$subjectCode];
            }
        }

        return $result;
    }

    public function validateSubjectCode(string $code): bool
    {
        return isset($this->subjects[$code]);
    }

    public function validateGroupCode(string $groupCode): bool
    {
        return isset($this->groups[$groupCode]);
    }
}
