<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role instanceof UserRole ? $this->role->value : $this->role,
            /**
             * Account creation timestamp.
             *
             * @var \Carbon\Carbon
             *
             * @example 2026-08-19T10:25:30Z
             */
            'created_at' => $this->created_at,
            /**
             * Account last update timestamp.
             *
             * @var \Carbon\Carbon
             *
             * @example 2026-08-19T10:25:30Z
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
