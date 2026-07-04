<?php

namespace App\Modules\Orders\Requests;

use App\Http\Requests\BaseFilterRequest;

/**
 * Class FilterRequest
 * @package App\Modules\Core\Requests
 */
class FilterRequest extends BaseFilterRequest
{
    public function rules(): array
    {
        // Lấy các rules chung từ BaseFilterRequest và gộp thêm rules riêng của Orders
        return array_merge(parent::rules(), [
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|gte:price_from',
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'price_from.numeric' => 'Giá từ phải là một số.',
            'price_to.numeric' => 'Giá đến phải là một số.',
            'price_to.gte' => 'Giá đến phải lớn hơn hoặc bằng giá từ.',
        ]);
    }

    /**
     * Tham số query chuẩn cho tài liệu Scribe.
     */
    public function queryParameters(): array
    {
        return array_merge(parent::queryParameters(), [
            'price_from' => [
                'description' => 'Lọc theo giá trị đơn hàng từ.',
                'example' => '100000',
            ],
            'price_to' => [
                'description' => 'Lọc theo giá trị đơn hàng đến.',
                'example' => '500000',
            ],
        ]);
    }
}
