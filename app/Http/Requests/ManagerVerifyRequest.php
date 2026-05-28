<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManagerVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'   => 'required|string',
            'password'   => 'required|string',
            'action'     => 'required|string|in:void,delete,discount,override,refund',
            'order_id'   => 'nullable|integer|exists:orders,id',
        ];
    }
}
