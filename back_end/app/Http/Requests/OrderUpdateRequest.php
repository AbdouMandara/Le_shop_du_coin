<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,paid,delivered,cancelled',
            'delivery' => 'sometimes|string',
            'payment_date' => 'sometimes|date',
        ];
    }
}
