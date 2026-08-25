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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedTinyInteger('telr')->default(1)->after('payfast_payment');
            $table->string('telr_store_id')->nullable()->after('telr');
            $table->string('telr_auth_key')->nullable()->after('telr_store_id');
            $table->string('telr_mode')->default('sandbox')->after('telr_auth_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['telr', 'telr_store_id', 'telr_auth_key', 'telr_mode']);
        });
    }
};
