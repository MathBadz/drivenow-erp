<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Snapshot of the vehicle (lives in fleet-service's own database).
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->string('vehicle_name');
            $table->string('vehicle_plate');
            $table->string('pickup_branch')->default('Main Branch');

            $table->date('pickup_date');
            $table->date('return_date');
            $table->unsignedSmallInteger('days')->default(1);
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
