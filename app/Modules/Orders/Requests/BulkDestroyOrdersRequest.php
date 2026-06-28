<?php

namespace App\Modules\Orders\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyOrdersRequest extends FormRequest
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
            // Sử dụng tên bảng 'orders' thay vì tên Model
            'ids.*' => 'required|integer|exists:orders,id'
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'   => 'Danh sách ID không được để trống.',
            'ids.array'      => 'Danh sách ID phải là một mảng.',
            'ids.*.integer'  => 'ID đơn hàng phải là số nguyên.',
            'ids.*.exists'   => 'Một hoặc nhiều đơn hàng không tồn tại trong hệ thống.',
        ];
    }
}
