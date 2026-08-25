<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_types', function (Blueprint $table) {
            //translation purpose
            $table->json('type_name')->nullable()->change();

            $table->string('country')->after('currency')->nullable();
            $table->string('state')->after('country')->nullable();
            $table->boolean('is_default')->after('is_active')->nullable();


        });

        try {
            Schema::table('delivery_types', function (Blueprint $table) {
                $table->dropUnique(['type_code']);
            });
        } catch (\Exception $e) {
            // Index doesn't exist, that's fine
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_types', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('state');
            $table->dropColumn('is_default');
        });
    }
};
