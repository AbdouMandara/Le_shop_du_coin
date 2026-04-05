<?php

namespace App\Http\Resources;

class UserResource extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'profile_photo' => $this->profile_photo
                                ? asset('storage/' . $this->profile_photo)
                                : null,
            'role' => $this->whenLoaded('role', function() {
                return $this->role->label;
            }),
            'is_active'     => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
