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
        Schema::table('maid_clearences', function (Blueprint $table) {
            $table->decimal('end_of_service_dh', 10, 2)->nullable();
            $table->decimal('other_dh', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maid_clearences', function (Blueprint $table) {
            $table->dropColumn(['end_of_service_dh', 'other_dh']);
        });
    }
};
