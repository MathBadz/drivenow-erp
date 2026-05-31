<?php

namespace App\Models;

use App\Enums\RentalStatus;
use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rental extends Model
{
    /** @use HasFactory<RentalFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'customer_name', 'customer_email', 'customer_phone',
        'vehicle_id', 'vehicle_name', 'vehicle_plate', 'pickup_branch',
        'pickup_date', 'return_date', 'days', 'daily_rate', 'subtotal', 'total',
        'status', 'notes', 'approved_at', 'released_at', 'returned_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RentalStatus::class,
            'pickup_date' => 'date',
            'return_date' => 'date',
            'days' => 'integer',
            'daily_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
            'returned_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Rental $rental): void {
            $rental->reference ??= 'RNT-'.strtoupper(Str::random(8));
        });
    }

    /**
     * @param  Builder<Rental>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('vehicle_name', 'like', "%{$term}%")
                ->orWhere('vehicle_plate', 'like', "%{$term}%");
        });
    }
}
