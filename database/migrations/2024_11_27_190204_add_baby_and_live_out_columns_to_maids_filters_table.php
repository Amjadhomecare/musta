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
        Schema::table('maids_filters', function (Blueprint $table) {
            $table->boolean('baby_0_to_6')->nullable();
            $table->boolean('baby_6_to_12')->nullable();
            $table->boolean('baby_1_to_2')->nullable();
            $table->boolean('baby_2_to_6')->nullable();
            $table->boolean('live_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maids_filters', function (Blueprint $table) {
            $table->dropColumn(['baby_0_to_6', 'baby_6_to_12', 'baby_1_to_2', 'baby_2_to_6', 'live_out']);
        });
    }
};
