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
        Schema::create('register_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('maid_name');
            $table->string('contract_ref');
            $table->string('customer_name');
            $table->string('memo');
            $table->string('status')->default('pending')->comment('pending, in progress, done');
            $table->string('type')->default('general')->comment('general, ranaway, urgent');
            $table->string('assigned_to')->nullable();
            $table->string('forward_to')->nullable();
            
            $table->string('created_by')->nullable();	
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->index('customer_name');
            $table->foreign('customer_name')->references('name')->on('customers')->onDelete('cascade');
            $table->index('maid_name');
            $table->foreign('maid_name')->references('name')->on('maids_d_b_s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_complaints');
    }
};
