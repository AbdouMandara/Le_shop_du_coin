<?php

namespace App\Http\Resources;

class UserResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->whenLoaded('role', function() {
                return $this->role->label;
            }),
            'created_at' => $this->created_at,
        ];
    }
}
