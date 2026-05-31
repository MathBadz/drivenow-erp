<?php

namespace App\Http\Resources;

use App\Models\CustomerActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CustomerActivity
 */
class CustomerActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
