<?php

namespace App\Modules\Orders\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'product_id'   => $this->product_id,
            // Sử dụng whenLoaded để chỉ hiển thị tên sản phẩm khi relationship 'product' đã được tải
            'product_name' => $this->whenLoaded('product', $this->product->name),
            'quantity'     => $this->quantity,
            'price'        => $this->price, // Giá tại thời điểm đặt hàng
        ];
    }
}
