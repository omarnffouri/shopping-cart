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
        Schema::table('user_delivery_types', function (Blueprint $table) {
            $table->decimal('delivery_price', 8, 2)->nullable();
            $table->string('delivery_time', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_delivery_types', function (Blueprint $table) {
            $table->dropColumn('delivery_price');
            $table->dropColumn('delivery_time');
        });
    }
};
