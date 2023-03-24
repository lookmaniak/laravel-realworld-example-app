<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use InvalidArgumentException;


class UpdateProfileRequest extends FormRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        if ($user === null) {
            throw new InvalidArgumentException('User not authenticated.');
        }

        return [
            'address' => 'string|max:1000',
            'province_id' => 'integer',
            'city_id' => 'integer',
            'district_id' => 'integer',
            'village_id' => 'integer',
            'postal_code' => 'string|max:8',
            'no_kontak' => 'string|max:16'
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validationData()
    {
        return Arr::wrap($this->input('profile'));
    }
}
