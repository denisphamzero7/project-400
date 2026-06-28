<?php

namespace App\Modules\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class importProductsRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules():array
    {
        return [
            'file'=>'required|mines:xlsx:xls,csv',
        ];
    }
    public function bodyparameters():array
    {
        return [
            'file'=>[
                'description'=>'File Excel dữ liệu khách hàng (.xlsx, .xls, .csv) cấu trúc cột phải khới với hệ thống'
            ]
        ];
    }
}
