<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class VillageResource extends JsonResource
{
    /**
    * The "data" wrapper that should be applied.
    *
    * @var string
    */
   public static $wrap = 'village';
   /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
    */
   public function toArray($request)
   {
       return [
           'id' => $this->resource->id,
           'nama' => $this->resource->nama,
       ];
   }
}
