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
        Schema::table('apply_visas', function (Blueprint $table) {
            $table->date('date_expiration')->nullable()
                ->comment('Expiration date to track when maid will get fine and last day allowed to stay in country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apply_visas', function (Blueprint $table) {
            $table->dropColumn('date_expiration');
        });
    }
};
