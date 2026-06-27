<?php

namespace App\Modules\Customers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Tên: Bắt buộc, là chuỗi, tối đa 255 ký tự
            'name' => 'required|string|max:255',
            
            // Email: Bắt buộc, đúng định dạng, tối đa 255 ký tự, và phải DUY NHẤT trong bảng customers
            'email' => 'required|email|max:255|unique:customers,email',
            
            // Điểm tích lũy: Không bắt buộc, nếu có truyền lên thì phải là số nguyên và >= 0
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
            'email.unique'   => 'Email này đã tồn tại trong hệ thống.',
            
            'loyalty_points.integer' => 'Điểm tích lũy phải là một số nguyên.',
            'loyalty_points.min'     => 'Điểm tích lũy không được nhỏ hơn 0.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Tên đầy đủ của khách hàng',
                'example' => 'Nguyễn Văn A'
            ],
            'email' => [
                'description' => 'Địa chỉ email liên hệ',
                'example' => 'nguyenvana@example.com'
            ],
            'loyalty_points' => [
                'description' => 'Điểm tích lũy ban đầu (Mặc định: 0)',
                'example' => 100
            ],
        ];
    }
}