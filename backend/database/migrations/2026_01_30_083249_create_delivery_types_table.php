<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name', 100);
            $table->string('type_code', 50)->unique();
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('AED');
            $table->text('description')->nullable();
            $table->integer('min_hours_advance')->default(0);
            $table->boolean('available_for_today')->default(true);
            $table->time('cutoff_time')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_types');
    }
};
