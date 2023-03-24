<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DistrictResourceCollection extends ResourceCollection
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'districts';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = DistrictResource::class;


    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function with($request)
    {
        return [
            'districts_count' => $this->collection->count(),
        ];
    }
}
