<?php

namespace App\Http\Resources;

use App\Models\CustomerFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CustomerFeedback
 */
class CustomerFeedbackResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
