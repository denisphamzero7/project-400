<?php

namespace App\Modules\Orders\Requests;

use Illuminate\Foundation\Http\FormRequest;

class importOrdersRequest extends FormRequest
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
                'description'=>'File Excel dữ liệu đơn hàng (.xlsx, .xls, .csv) cấu trúc cột phải khớp với hệ thống'
            ]
        ];
    }
}
