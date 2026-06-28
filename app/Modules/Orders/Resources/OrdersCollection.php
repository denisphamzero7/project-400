<?php

namespace App\Modules\Orders\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class OrdersCollection extends ResourceCollection
{
    public $collects = OrdersResource::class;

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
