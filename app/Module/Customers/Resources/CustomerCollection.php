<?php

namespace App\Modules\Customers\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class CustomerCollection extends ResourceCollection
{
    public $collects = CustomersResource::class;

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
