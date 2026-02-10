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
        Schema::table('maids_d_b_s', function (Blueprint $table) {
            $table->string('uid', 30)->nullable()->after('id'); 
            // You can remove ->nullable() if it should always be required
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maids_d_b_s', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};
