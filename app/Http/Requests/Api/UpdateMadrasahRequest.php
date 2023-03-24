<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Arr;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMadrasahRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request become good.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'kode_jenjang' => 'required|string:max(32)',
            'jenjang_id' => 'required|integer',
            'nama' => 'string:max(255)',
            'npsn' => 'string:max(19)',
            'alamat' => 'string:max(1000)',
            'nama_kepsek' => 'string:max(255)',
        ];
    }

    /**
     * Wrap non array or null value
     * 
     * @return array<mixed>
     */
    public function validationData()
    {
        return Arr::wrap($this->input('madrasah'));
    }
}
