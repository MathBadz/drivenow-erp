<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_name', 'customer_email', 'rental_reference',
        'rental_id', 'subtotal', 'penalty', 'discount', 'total', 'amount_paid',
        'balance', 'status', 'due_date', 'issued_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'penalty' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'due_date' => 'date',
            'issued_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            $invoice->invoice_number ??= 'INV-'.strtoupper(Str::random(8));
        });
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    /**
     * Recalculate totals/status from the current payments and charges.
     */
    public function recalculate(): void
    {
        $this->total = max(0, (float) $this->subtotal + (float) $this->penalty - (float) $this->discount);
        $this->amount_paid = (float) $this->payments()->sum('amount');
        $this->balance = max(0, (float) $this->total - (float) $this->amount_paid);

        if ($this->status !== InvoiceStatus::Refunded) {
            $this->status = match (true) {
                $this->balance <= 0 && $this->amount_paid > 0 => InvoiceStatus::Paid,
                $this->amount_paid > 0 => InvoiceStatus::Partial,
                $this->due_date !== null && $this->due_date->isPast() => InvoiceStatus::Overdue,
                default => InvoiceStatus::Unpaid,
            };
        }

        $this->save();
    }

    /**
     * @param  Builder<Invoice>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('invoice_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('rental_reference', 'like', "%{$term}%");
        });
    }
}
