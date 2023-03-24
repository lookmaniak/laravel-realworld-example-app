<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseProfileResource extends JsonResource
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
            'address' => $this->resource->address,
            'postal_code' => $this->resource->postal_code,
            'no_kontak' => $this->resource->no_kontak,
            'province' => new ProvinceResource($this->resource->province),
            'city' => new CityResource($this->resource->city),
            'district' => new DistrictResource($this->resource->district),
            'village' => new VillageResource($this->resource->village),
        ];
    }
}
