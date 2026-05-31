<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('rental_reference')->nullable()->index();
            $table->unsignedBigInteger('rental_id')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('penalty', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('unpaid')->index();
            $table->date('due_date')->nullable();
            $table->date('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('method')->default('cash');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
