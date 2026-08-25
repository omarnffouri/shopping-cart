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

        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_delivery_type_id')->after('quantity')->nullable()->constrained();
        });
        try {
            Schema::table('user_delivery_types', function (Blueprint $table) {
                $table->dropForeign('user_delivery_types_cart_id_foreign');
                $table->dropColumn('cart_id');
            });
        } catch (Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_delivery_type_id']);
            $table->dropColumn('user_delivery_type_id');
        });
    }
};
