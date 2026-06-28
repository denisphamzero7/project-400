<?php

namespace App\Modules\Products\Requests;

use App\Enums\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Tên sản phẩm: Bắt buộc, chuỗi, tối đa 255 ký tự
            'name' => 'required|string|max:255',

            // Giá sản phẩm: Bắt buộc, là số (numeric hỗ trợ cả số thập phân), >= 0
            'price' => 'required|numeric|min:0',

            // Tồn kho: Bắt buộc, là số nguyên, >= 0 (Không thể có nửa sản phẩm hoặc tồn kho âm)
            'stock_quantity' => 'required|integer|min:0',

            // Trạng thái: không Bắt buộc, phải nằm trong danh sách ProductStatusEnum
            'status' => ['nullable', Rule::enum(ProductStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.string'   => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max'      => 'Tên sản phẩm không được vượt quá 255 ký tự.',

            'price.required' => 'Vui lòng nhập giá sản phẩm.',
            'price.numeric'  => 'Giá sản phẩm phải là một số hợp lệ.',
            'price.min'      => 'Giá sản phẩm không được nhỏ hơn 0.',

            'stock_quantity.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock_quantity.integer'  => 'Số lượng tồn kho phải là số nguyên.',
            'stock_quantity.min'      => 'Số lượng tồn kho không được nhỏ hơn 0.',

            'status' => 'Vui lòng chọn trạng thái sản phẩm.',
        ];
    }

    /**
     * Thông tin body parameters dành cho thư viện sinh tài liệu API (Scribe / Swagger)
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Tên của sản phẩm',
                'example' => 'Laptop Dell XPS 15 2024'
            ],
            'price' => [
                'description' => 'Giá bán của sản phẩm (VNĐ)',
                'example' => 35000000
            ],
            'stock_quantity' => [
                'description' => 'Số lượng sản phẩm hiện có trong kho',
                'example' => 50
            ],
            'status' => [
                'description' => 'Trạng thái sản phẩm',
                'example' => 'active' // Hoặc giá trị tương ứng trong ProductStatusEnum của bạn
            ],
        ];
    }
}
