<?php

namespace App\Modules\Orders\Requests;

use App\Enums\OrdersStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|required|integer|exists:customers,id',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status' => ['sometimes', 'nullable', Rule::enum(OrdersStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'customer_id.integer' => 'Mã khách hàng không hợp lệ.',
            'customer_id.exists' => 'Khách hàng không tồn tại.',

            'total_amount.required' => 'Vui lòng nhập tổng tiền.',
            'total_amount.numeric'  => 'Tổng tiền phải là một số hợp lệ.',
            'total_amount.min'      => 'Tổng tiền không được nhỏ hơn 0.',

            'status' => 'Vui lòng chọn trạng thái đơn hàng.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'customer_id' => [
                'description' => 'Mã khách hàng',
                'example' => 1
            ],
            'total_amount' => [
                'description' => 'Tổng tiền đơn hàng (VNĐ)',
                'example' => 550000
            ],
            'status' => [
                'description' => 'Trạng thái đơn hàng',
                'example' => 'completed'
            ],
        ];
    }
}
