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
        Schema::table('returned_maids', function (Blueprint $table) {
            $table->index('maid_return_name');
            $table->index('contract');
            $table->index('returned_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returned_maids', function (Blueprint $table) {
            $table->dropIndex(['maid_return_name']);
            $table->dropIndex(['contract']);
            $table->dropIndex(['returned_date']);
        });
    }
};
