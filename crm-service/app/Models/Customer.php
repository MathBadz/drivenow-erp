<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerTier;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'city', 'status', 'tier',
        'loyalty_points', 'total_rentals', 'total_spent', 'blacklist_reason',
        'joined_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'tier' => CustomerTier::class,
            'loyalty_points' => 'integer',
            'total_rentals' => 'integer',
            'total_spent' => 'decimal:2',
            'joined_at' => 'date',
        ];
    }

    /**
     * @return HasMany<CustomerActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class)->latest();
    }

    /**
     * @return HasMany<CustomerFeedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(CustomerFeedback::class)->latest();
    }

    /**
     * @param  Builder<Customer>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function log(string $type, string $description): void
    {
        $this->activities()->create(['type' => $type, 'description' => $description]);
    }
}
