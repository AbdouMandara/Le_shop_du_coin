<?php

namespace App\Http\Resources;

class FavoriteResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
