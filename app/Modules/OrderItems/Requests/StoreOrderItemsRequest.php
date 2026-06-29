<?php

namespace App\Modules\OrderItems\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemsRequest extends FormRequest
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
            'order_id'   => 'required|integer|exists:orders,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_id.required'   => 'Vui lòng truyền mã đơn hàng.',
            'order_id.integer'    => 'Mã đơn hàng phải là số nguyên.',
            'order_id.exists'     => 'Đơn hàng này không tồn tại trong hệ thống.',

            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.integer'  => 'Mã sản phẩm phải là số nguyên.',
            'product_id.exists'   => 'Sản phẩm không tồn tại trong hệ thống.',

            'quantity.required'   => 'Vui lòng nhập số lượng.',
            'quantity.integer'    => 'Số lượng phải là một số nguyên.',
            'quantity.min'        => 'Số lượng phải lớn hơn hoặc bằng 1.',

            'price.required'      => 'Vui lòng nhập giá sản phẩm.',
            'price.numeric'       => 'Giá sản phẩm phải là một số hợp lệ.',
            'price.min'           => 'Giá sản phẩm không được nhỏ hơn 0.',
        ];
    }

    /**
     * Thông tin body parameters dành cho thư viện sinh tài liệu API (Scribe / Swagger)
     */
    public function bodyParameters(): array
    {
        return [
            'order_id' => [
                'description' => 'Mã đơn hàng',
                'example'     => 10
            ],
            'product_id' => [
                'description' => 'Mã sản phẩm',
                'example'     => 5
            ],
            'quantity' => [
                'description' => 'Số lượng sản phẩm mua',
                'example'     => 2
            ],
            'price' => [
                'description' => 'Đơn giá của sản phẩm (VNĐ)',
                'example'     => 250000.00
            ],
        ];
    }
}
