<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ordered_products_notes', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('message', 255);

            $table->unsignedInteger('ordered_product_id');
            $table->foreign('ordered_product_id')
                ->references('id')
                ->on('ordered_products')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordered_products_notes');
    }
};
