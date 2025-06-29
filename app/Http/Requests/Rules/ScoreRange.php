<?php
namespace App\Http\Requests\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ScoreRange implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) return;

        if (!is_numeric($value) || $value < 0 || $value > 10) {
            $fail('Điểm phải từ 0 đến 10.');
        }
    }
}
