<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id'             => 'required|integer|exists:restaurant_tables,id',
            'items'                => 'nullable|array',
            'items.*.product_id'   => 'required_with:items|integer|exists:products,id',
            'items.*.quantity'     => 'required_with:items|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:255',
        ];
    }
}
