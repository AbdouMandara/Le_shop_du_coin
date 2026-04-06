<?php

namespace App\Http\Resources;

class ProductResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_price' => $this->price,
            'price' => $this->final_price,
            'discounted_price' => $this->final_price,
            'active_promotion' => $this->active_promotion,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'image' => (str_starts_with($this->image, 'http')) ? $this->image : ($this->image ? asset('storage/' . $this->image) : null),
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
