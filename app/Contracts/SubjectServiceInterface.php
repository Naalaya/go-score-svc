<?php
namespace App\Contracts;

interface SubjectServiceInterface
{
    public function getAllSubjects(): array;
    public function getSubjectByCode(string $code): ?array;
    public function getSubjectsByGroup(string $groupCode): array;
    public function validateSubjectCode(string $code): bool;
    public function validateGroupCode(string $groupCode): bool;
}
