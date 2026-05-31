<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('plate_number')->unique();
            $table->string('category')->index();
            $table->string('status')->default('available')->index();
            $table->string('branch')->default('Main Branch');
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->unsignedTinyInteger('seats')->default(5);
            $table->string('transmission')->default('Automatic');
            $table->string('fuel_type')->default('Gasoline');
            $table->string('color')->nullable();
            $table->unsignedInteger('mileage')->default(0);
            $table->string('image_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
