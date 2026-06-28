<?php

namespace App\Modules\Products\Requests;

use App\Enums\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Với Products hiện tại không có trường unique (như email của customer)
        // nên không cần lấy ID từ route để ignore nữa.

        return [
            // Dùng 'sometimes' để chỉ validate nếu field này có được gửi lên (Hỗ trợ PATCH update một phần)
            'name'           => 'sometimes|required|string|max:255',

            'price'          => 'sometimes|required|numeric|min:0',

            'stock_quantity' => 'sometimes|required|integer|min:0',

            // Trạng thái vẫn không bắt buộc, nhưng nếu gửi lên phải đúng Enum
            'status'         => ['sometimes', 'nullable', Rule::enum(ProductStatusEnum::class)],
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
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Tên của sản phẩm',
                'example' => 'Laptop Dell XPS 15 2024 (Bản nâng cấp)'
            ],
            'price' => [
                'description' => 'Giá bán cập nhật của sản phẩm (VNĐ)',
                'example' => 34000000
            ],
            'stock_quantity' => [
                'description' => 'Số lượng sản phẩm tồn kho cập nhật',
                'example' => 45
            ],
            'status' => [
                'description' => 'Trạng thái sản phẩm cập nhật (Không bắt buộc)',
                'example' => 'inactive'
            ],
        ];
    }
}
