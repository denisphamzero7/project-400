<?php

namespace App\Modules\OrderItems\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class OrderItemsCollection extends ResourceCollection
{
    public $collects = OrderItemsResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
