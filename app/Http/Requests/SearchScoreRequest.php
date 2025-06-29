<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sbd' => 'required|string|regex:/^[0-9]{8,10}$/',
            'year' => 'sometimes|integer|between:2020,2025',
            'include_statistics' => 'sometimes|boolean',
            'include_metadata' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'sbd.required' => 'Số báo danh là bắt buộc',
            'sbd.regex' => 'Số báo danh phải gồm 8-10 chữ số',
            'year.integer' => 'Năm phải là số nguyên',
            'year.between' => 'Năm phải từ 2020 đến 2025',
            'include_statistics.boolean' => 'include_statistics phải là true hoặc false',
            'include_metadata.boolean' => 'include_metadata phải là true hoặc false',
        ];
    }

    /**
     * Get the validated student registration number.
     */
    public function getSbd(): string
    {
        return $this->validated()['sbd'];
    }

    /**
     * Get the validated year.
     */
    public function getYear(): ?int
    {
        return $this->validated()['year'] ?? null;
    }

    /**
     * Check if statistics should be included.
     */
    public function shouldIncludeStatistics(): bool
    {
        return $this->validated()['include_statistics'] ?? false;
    }

    /**
     * Check if metadata should be included.
     */
    public function shouldIncludeMetadata(): bool
    {
        return $this->validated()['include_metadata'] ?? false;
    }
}
