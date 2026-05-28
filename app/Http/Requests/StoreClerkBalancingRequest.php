<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClerkBalancingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_5000' => 'required|integer|min:0',
            'note_2000' => 'required|integer|min:0',
            'note_1000' => 'required|integer|min:0',
            'note_500'  => 'required|integer|min:0',
            'note_200'  => 'required|integer|min:0',
            'note_100'  => 'required|integer|min:0',
            'note_50'   => 'required|integer|min:0',
            'note_20'   => 'required|integer|min:0',
            'note_10'   => 'required|integer|min:0',
            'coin_5'    => 'required|integer|min:0',
            'coin_2'    => 'required|integer|min:0',
            'coin_1'    => 'required|integer|min:0',
            'coin_50c'  => 'required|integer|min:0',
            'notes'     => 'nullable|string|max:1000',
        ];
    }
}
