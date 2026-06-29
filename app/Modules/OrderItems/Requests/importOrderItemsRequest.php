<?php

namespace App\Modules\OrderItems\Requests;

use Illuminate\Foundation\Http\FormRequest;

class importOrderItemsRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules():array
    {
        return [
            'file'=>'required|mimes:xlsx,xls,csv',
        ];
    }
    public function bodyparameters():array
    {
        return [
            'file'=>[
                'description'=>'File Excel dữ liệu chi tiết đơn hàng (.xlsx, .xls, .csv) cấu trúc cột phải khớp với hệ thống'
            ]
        ];
    }
}
