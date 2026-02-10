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
        Schema::table('all_account_ledger__d_b_s', function (Blueprint $table) {
            $table->string('sub_class')->nullable()->after('class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('all_account_ledger__d_b_s', function (Blueprint $table) {
            $table->dropColumn('sub_class');
        });
    }
};
