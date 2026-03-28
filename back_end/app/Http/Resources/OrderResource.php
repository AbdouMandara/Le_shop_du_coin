<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'status' => $this->status,
            'delivery' => $this->delivery,
            'delivery_location' => $this->delivery_location,
            'payment_date' => $this->payment_date,
            'user' => new UserResource($this->whenLoaded('user')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'livreur' => new UserResource($this->whenLoaded('livreur')),
            'created_at' => $this->created_at,
        ];
    }
}
