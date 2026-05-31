<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        Invoice::factory()->count(50)->create()->each(function (Invoice $invoice): void {
            $roll = random_int(1, 100);

            if ($roll <= 45) {
                $this->pay($invoice, (float) $invoice->total);          // fully paid
            } elseif ($roll <= 65) {
                $this->pay($invoice, round((float) $invoice->total * 0.4, 2)); // partial
            }

            $invoice->recalculate();
        });

        // A few refunded invoices.
        Invoice::factory()->count(4)->create()->each(function (Invoice $invoice): void {
            $this->pay($invoice, (float) $invoice->total);
            $invoice->recalculate();
            $invoice->update(['status' => InvoiceStatus::Refunded->value]);
        });
    }

    private function pay(Invoice $invoice, float $amount): void
    {
        $invoice->payments()->create([
            'method' => fake()->randomElement(PaymentMethod::values()),
            'amount' => $amount,
            'reference' => strtoupper(fake()->bothify('PAY-####??')),
            'paid_at' => now(),
        ]);
    }
}
