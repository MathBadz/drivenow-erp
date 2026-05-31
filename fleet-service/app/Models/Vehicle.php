<?php

namespace App\Models;

use App\Enums\VehicleCategory;
use App\Enums\VehicleStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'make', 'model', 'year', 'plate_number', 'category', 'status', 'branch',
        'daily_rate', 'seats', 'transmission', 'fuel_type', 'color', 'mileage',
        'image_url', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'category' => VehicleCategory::class,
            'status' => VehicleStatus::class,
            'year' => 'integer',
            'seats' => 'integer',
            'mileage' => 'integer',
            'daily_rate' => 'decimal:2',
        ];
    }

    /**
     * Full display name, e.g. "2023 Toyota Vios".
     */
    public function name(): string
    {
        return trim("{$this->year} {$this->make} {$this->model}");
    }

    /**
     * Scope: free-text search across make, model and plate.
     *
     * @param  Builder<Vehicle>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('make', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('plate_number', 'like', "%{$term}%");
        });
    }
}
