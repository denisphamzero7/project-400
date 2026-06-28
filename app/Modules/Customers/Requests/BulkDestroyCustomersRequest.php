<?php

namespace App\Modules\Customers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tạm thời return true do hệ thống không có tính năng Authentication/Authorization
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array',
            // Sử dụng tên bảng 'customers' thay vì tên Model
            'ids.*' => 'required|integer|exists:customers,id'
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'   => 'Danh sách ID không được để trống.',
            'ids.array'      => 'Danh sách ID phải là một mảng.',
            'ids.*.integer'  => 'ID khách hàng phải là số nguyên.',
            'ids.*.exists'   => 'Một hoặc nhiều khách hàng không tồn tại trong hệ thống.',
        ];
    }
}
