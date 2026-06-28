<?php

namespace App\Modules\Customers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của khách hàng đang được sửa từ URL (ví dụ: PUT /api/customers/{customer})
        // Chữ 'customer' phải khớp với tên tham số định tuyến trong file routes.
        $customerId = $this->route('customer');

        return [
            // Dùng 'sometimes' để chỉ validate nếu field này có được gửi lên
            'name' => 'sometimes|required|string|max:255',

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                // Bỏ qua ID của khách hàng hiện tại khi kiểm tra trùng lặp email
                Rule::unique('customers', 'email')->ignore($customerId),
            ],

            'loyalty_points' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên khách hàng.',
            'name.string'   => 'Tên khách hàng phải là chuỗi ký tự.',
            'name.max'      => 'Tên khách hàng không được vượt quá 255 ký tự.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Địa chỉ email không đúng định dạng.',
            'email.max'      => 'Email không được vượt quá 255 ký tự.',
            'email.unique'   => 'Email này đã được sử dụng bởi một khách hàng khác.',

            'loyalty_points.integer' => 'Điểm tích lũy phải là một số nguyên.',
            'loyalty_points.min'     => 'Điểm tích lũy không được nhỏ hơn 0.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Tên đầy đủ của khách hàng',
                'example' => 'Nguyễn Văn B'
            ],
            'email' => [
                'description' => 'Địa chỉ email liên hệ',
                'example' => 'nguyenvanb@example.com'
            ],
            'loyalty_points' => [
                'description' => 'Điểm tích lũy cập nhật',
                'example' => 150
            ],
        ];
    }
}
