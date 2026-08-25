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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('refund_status', 20)
                ->nullable()
                ->after('cancelled')
                ->comment('pending | refunded | failed');

            $table->string('refund_ref')
                ->nullable()
                ->after('refund_status')
                ->comment('Telr refund transaction reference');

            $table->json('refund_response')
                ->nullable()
                ->after('refund_ref')
                ->comment('Raw Telr refund API response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'refund_status',
                'refund_ref',
                'refund_response',
            ]);
        });
    }
};
