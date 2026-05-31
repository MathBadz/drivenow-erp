<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('tier')->default('regular')->index();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->unsignedInteger('total_rentals')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->string('blacklist_reason')->nullable();
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('note');
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customers');
    }
};
