<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     * 
     * 
     * 
     * 
     * 
     */
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role_id' => $this->role_id,
            'photo_profile' => $this->photo_profile,
            'date_of_birth' => $this->date_of_birth,
            'is_active' => $this->is_active,
            'role'  => new RoleResource($this->whenLoaded('role')),
        ];
    }
}
