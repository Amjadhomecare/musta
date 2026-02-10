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
            $table->string('salary_details')->nullable()->default('14 days');
            $table->string('end_of_service_details')->nullable()->default('21 days for each YEAR');
            $table->string('other_details')->nullable()->default('-');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maid_clearences', function (Blueprint $table) {
            $table->dropColumn(['salary_details', 'end_of_service_details', 'other_details']);
        });
    }
};
