<?php

namespace App\Modules\OrderItems\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
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
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'product_id'   => $this->product_id,
            // Tự động lấy tên sản phẩm nếu đã gọi with('product')
            'product_name' => $this->whenLoaded('product', fn() => $this->product->name),
            'quantity'     => $this->quantity,
            'price'        => (float) $this->price,
            // Không gọi created_at và updated_at vì model OrderItem đã tắt $timestamps
        ];
    }
}
