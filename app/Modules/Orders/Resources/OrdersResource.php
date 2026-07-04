<?php

namespace App\Modules\Orders\Resources;

use App\Modules\Orders\Resources\OrderItemResource;

use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
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
            'id'             => $this->id,
            'customer_id'    => $this->customer_id,
            'customer_name'  => $this->whenLoaded('customer', $this->customer->name),
            'total_amount'   => $this->total_amount,
            'status'         => is_object($this->status) ? $this->status->value : $this->status,
            // Sử dụng whenLoaded và collection để trả về danh sách các sản phẩm
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
