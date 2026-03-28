<?php

namespace App\Http\Requests;

// use Illuminate\Contracts\Validation\ValidationRule;'product_id' => 'required|exists:products,id
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'delivery' => 'sometimes|boolean',
            'delivery_location' => 'nullable|string',
        ];
    }
}
