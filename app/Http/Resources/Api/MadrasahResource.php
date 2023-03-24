<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MadrasahResource extends JsonResource
{
    /**
    * The "data" wrapper that should be applied.
    *
    * @var string
    */
   public static $wrap = 'madrasah';
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
           'kode_jenjang' => $this->resource->kode_jenjang,
           'jenjang_id' => $this->resource->jenjang_id,
           'nama' => $this->resource->nama,
           'npsn' => $this->resource->npsn,
           'alamat' => $this->resource->alamat,
           'nama_kepsek' => $this->resource->nama_kepsek,
       ];
   }
}
