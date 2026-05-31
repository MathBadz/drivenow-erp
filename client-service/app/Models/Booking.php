<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'reference', 'vehicle_id', 'vehicle_name', 'vehicle_category',
        'vehicle_plate', 'daily_rate', 'pickup_date', 'return_date', 'days',
        'total', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'daily_rate' => 'decimal:2',
            'total' => 'decimal:2',
            'days' => 'integer',
            'pickup_date' => 'date',
            'return_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->reference ??= 'BK-'.strtoupper(Str::random(8));
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
