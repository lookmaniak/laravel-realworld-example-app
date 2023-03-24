<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserAccountResourceCollection extends ResourceCollection
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'user_accounts';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = UserAccountResource::class;

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function with($request)
    {
        return [
            'accounts_count' => $this->collection->count(),
            'user' => new UserResource(\Auth::user()),
        ];
    }
}
