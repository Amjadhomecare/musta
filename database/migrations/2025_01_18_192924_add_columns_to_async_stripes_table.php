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
            $table->string('cus_str_id')->after('stripe_id')->nullable(); // Add after 'stripe_id'
            $table->integer('refunded_amount')->after('amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('async_stripes', function (Blueprint $table) {
            $table->dropColumn(['cus_str_id', 'refunded_amount']);
        });
    }
};
