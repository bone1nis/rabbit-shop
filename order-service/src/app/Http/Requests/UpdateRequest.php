<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',

            'products' => 'sometimes|array',
            'products.*.id' => 'required|integer',
            'products.*.name' => 'sometimes|string|max:255',
            'products.*.price' => 'sometimes|numeric|min:0',
            'products.*.quantity' => 'sometimes|integer|min:1',
        ];
    }
}
