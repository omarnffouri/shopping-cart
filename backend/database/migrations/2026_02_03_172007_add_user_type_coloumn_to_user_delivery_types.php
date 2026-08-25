<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_delivery_types', function (Blueprint $table) {
            // Drop user foreign key first, then the column
            $table->dropForeign('user_delivery_types_user_id_foreign');
            $table->dropColumn('user_id');

            // Add columns
            $table->nullableMorphs('userable');

            $table->unsignedInteger('cart_id')->nullable();

            // Add foreign key constraint
            $table->foreign('cart_id')
                ->references('id')
                ->on('carts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_delivery_types', function (Blueprint $table) {
            $table->dropMorphs('userable');
            $table->dropForeign('user_delivery_types_cart_id_foreign');
            $table->dropColumn('cart_id');

            // Revert user foreign key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }
};
