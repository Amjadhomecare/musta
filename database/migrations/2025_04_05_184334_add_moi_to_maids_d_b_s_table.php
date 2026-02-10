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
            $table->string('moi')->nullable();
            $table->date('start_as_p4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maids_d_b_s', function (Blueprint $table) {
            $table->dropColumn('moi');
            $table->dropColumn('start_as_p4');
        });
    }
};
