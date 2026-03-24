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
            'delivery' => 'sometimes|string', // sometimes est une méthode qui permet de dire que si ce champ est présent je lui applique les conditions de validation comme dans notre cadre c'est string, et si ce champ n'est pas envoyé ça ne dérange pas
            'payment_date' => 'sometimes|date',
        ];
    }
}
