<?php
namespace App\Http\Requests\Rules;
use App\Contracts\SubjectServiceInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSubjectCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $subjectService = app(SubjectServiceInterface::class);
        if (!$subjectService->validateSubjectCode($value)) {
            $fail('Mã môn học không hợp lệ.');
        }
    }
}
