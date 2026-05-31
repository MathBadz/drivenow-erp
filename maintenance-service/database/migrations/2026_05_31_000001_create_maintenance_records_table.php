<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->string('vehicle_name');
            $table->string('vehicle_plate');
            $table->string('type')->default('inspection')->index();
            $table->string('status')->default('scheduled')->index();
            $table->string('severity')->default('low');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->unsignedInteger('odometer')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
