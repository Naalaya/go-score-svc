<?php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_code' => 'sometimes|string|in:A,B,C,D',
            'subject_codes' => 'sometimes|array',
            'subject_codes.*' => 'string|in:toan,ngu_van,ngoai_ngu,vat_li,hoa_hoc,sinh_hoc,lich_su,dia_li,gdcd',
            'include_percentages' => 'sometimes|in:true,false,1,0',
            'format' => 'sometimes|string|in:json,csv',
        ];
    }

    /**
     * Get the validation data for the request.
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        // Convert string boolean values from query params to actual booleans
        if (isset($data['include_percentages'])) {
            $data['include_percentages'] = filter_var($data['include_percentages'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'group_code.in' => 'Mã khối phải là A, B, C hoặc D',
            'subject_codes.array' => 'Danh sách môn học phải là mảng',
            'subject_codes.*.in' => 'Mã môn học không hợp lệ',
            'format.in' => 'Định dạng phải là json hoặc csv',
        ];
    }
}
