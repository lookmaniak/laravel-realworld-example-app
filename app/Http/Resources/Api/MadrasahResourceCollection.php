<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MadrasahResourceCollection extends ResourceCollection
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'madrasahs';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = MadrasahResource::class;

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function with($request)
    {
        return [
            'madrasah_count' => $this->collection->count(),
        ];
    }
}
