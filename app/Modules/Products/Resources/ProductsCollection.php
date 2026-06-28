<?php

namespace App\Modules\Products\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class ProductsCollection extends ResourceCollection
{
    public $collects = ProductsResource::class;

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
