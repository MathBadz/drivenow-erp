<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('vehicle_name');
            $table->string('vehicle_category')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->date('pickup_date');
            $table->date('return_date');
            $table->unsignedSmallInteger('days')->default(1);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
