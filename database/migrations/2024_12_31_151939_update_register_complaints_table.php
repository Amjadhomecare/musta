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
        Schema::table('register_complaints', function (Blueprint $table) {
            // Making the specified columns nullable
            $table->string('maid_name')->nullable()->change();
            $table->string('contract_ref')->nullable()->change();
            $table->string('customer_name')->nullable()->change();
            $table->string('memo')->nullable()->change();

            // Adding the action_taken column
            $table->json('action_taken')->nullable()->comment('Stores actions taken as a JSON array');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('register_complaints', function (Blueprint $table) {
            // Reverting the columns to not nullable
            $table->string('maid_name')->nullable(false)->change();
            $table->string('contract_ref')->nullable(false)->change();
            $table->string('customer_name')->nullable(false)->change();
            $table->string('memo')->nullable(false)->change();

            // Dropping the action_taken column
            $table->dropColumn('action_taken');
        });
    }
};
