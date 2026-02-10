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
        Schema::table('async_stripes', function (Blueprint $table) {
            $table->string('receipt_url')->nullable();
            $table->string('rv_erp')->nullable();
            $table->string('customer_from')->nullable();
            $table->boolean('refunded')->nullable();
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('async_stripes', function (Blueprint $table) {
            $table->dropColumn(['receipt_url', 'rv_erp', 'customer_from', 'refunded']);
        });
    }
};
