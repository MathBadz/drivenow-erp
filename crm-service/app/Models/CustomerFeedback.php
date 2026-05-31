<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    protected $table = 'customer_feedback';

    protected $fillable = ['customer_id', 'rating', 'comment'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
