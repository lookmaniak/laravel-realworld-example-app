<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends BaseProfileResource
{
    /**
    * The "data" wrapper that should be applied.
    *
    * @var string
    */
   public static $wrap = 'profile';
   /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
    */
   public function toArray($request)
   {
        return array_merge(parent::toArray($request), [
            'data' => [],
        ]);
   }
}
