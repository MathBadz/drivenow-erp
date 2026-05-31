<?php

namespace App\Models;

use App\Enums\MaintenanceSeverity;
use App\Enums\MaintenanceStatus;
use App\Enums\MaintenanceType;
use Database\Factories\MaintenanceRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaintenanceRecord extends Model
{
    /** @use HasFactory<MaintenanceRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'vehicle_id', 'vehicle_name', 'vehicle_plate', 'type',
        'status', 'severity', 'title', 'description', 'cost', 'odometer',
        'scheduled_date', 'completed_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => MaintenanceType::class,
            'status' => MaintenanceStatus::class,
            'severity' => MaintenanceSeverity::class,
            'cost' => 'decimal:2',
            'odometer' => 'integer',
            'scheduled_date' => 'date',
            'completed_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MaintenanceRecord $record): void {
            $record->reference ??= 'MNT-'.strtoupper(Str::random(8));
        });
    }

    /**
     * @param  Builder<MaintenanceRecord>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('vehicle_name', 'like', "%{$term}%")
                ->orWhere('vehicle_plate', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%");
        });
    }
}
