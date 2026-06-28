<?php

namespace App\Modules\Products\Controllers;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FilterRequest
 * @package App\Modules\Core\Requests
 *
 * @property-read string|null $search Từ khóa tìm kiếm.
 * @property-read int|null $limit Số lượng mục trên mỗi trang.
 */
class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'sort_by' => 'nullable|string|max:50',
            'sort_order' => 'nullable|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Từ khóa tìm kiếm phải là một chuỗi ký tự.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',
            'status.string' => 'Trạng thái phải là một chuỗi ký tự.',
            'sort_by.in' => 'Trường sắp xếp không hợp lệ.',
            'sort_order.in' => 'Thứ tự sắp xếp không hợp lệ.',
            'limit.integer' => 'Số lượng phải là một số nguyên.',
            'limit.min' => 'Số lượng phải lớn hơn 0.',
            'limit.max' => 'Số lượng phải nhỏ hơn 100.',
        ];
    }

    /**
     * Tham số query chuẩn cho tài liệu Scribe.
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Từ khóa tìm kiếm theo tên hoặc trường chính.',
                'example' => 'van-ban',
            ],
            'status' => [
                'description' => 'Lọc theo trạng thái.',
                'example' => 'active',
            ],
            'from_date' => [
                'description' => 'Lọc từ ngày tạo (Y-m-d).',
                'example' => '2026-01-01',
            ],
            'to_date' => [
                'description' => 'Lọc đến ngày tạo (Y-m-d).',
                'example' => '2026-12-31',
            ],
            'sort_by' => [
                'description' => 'Trường dùng để sắp xếp.',
                'example' => 'created_at',
            ],
            'sort_order' => [
                'description' => 'Thứ tự sắp xếp.',
                'example' => 'desc',
            ],
            'limit' => [
                'description' => 'Số bản ghi mỗi trang (1-100).',
                'example' => 10,
            ],
        ];
    }

    /**
     * Tránh cảnh báo của Scribe với request dùng cho query.
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
