<?php

namespace App\Modules\Orders\Requests;

use App\Enums\OrdersStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'status' => ['nullable', Rule::enum(OrdersStatusEnum::class)],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'customer_id.integer' => 'Mã khách hàng không hợp lệ.',
            'customer_id.exists' => 'Khách hàng không tồn tại.',

            'status' => 'Vui lòng chọn trạng thái đơn hàng.',

            'items.required' => 'Đơn hàng phải có ít nhất một sản phẩm.',
            'items.array' => 'Danh sách sản phẩm không hợp lệ.',
            'items.*.product_id.required' => 'Vui lòng cung cấp mã sản phẩm.',
            'items.*.product_id.exists' => 'Một trong các sản phẩm không tồn tại.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng cho sản phẩm.',
            'items.*.quantity.min' => 'Số lượng sản phẩm phải lớn hơn 0.',
        ];
    }

    /**
     * Thông tin body parameters dành cho thư viện sinh tài liệu API (Scribe / Swagger)
     */
    public function bodyParameters(): array
    {
        return [
            'customer_id' => [
                'description' => 'Mã khách hàng',
                'example' => 1
            ],
            'status' => [
                'description' => 'Trạng thái đơn hàng',
                'example' => 'pending'
            ],
            'items' => [
                'description' => 'Danh sách các sản phẩm trong đơn hàng.',
                'example' => [
                    [
                        'product_id' => 1,
                        'quantity' => 2
                    ],
                    [
                        'product_id' => 2,
                        'quantity' => 1
                    ]
                ]
            ]
        ];
    }
}
