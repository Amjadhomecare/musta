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
        Schema::create('pre_connection_invoice_d_b_s', function (Blueprint $table) {
            $table->id();
            $table->string('group')->require();
            $table->string('invoice_connection_name')->require();	
            $table->string('type')->require();
            $table->string('ledger')->require();	
            $table->decimal('amount', 8, 2)->require();	
            $table->string('notes')->nullable();
            $table->decimal('total_credit')->require();
            $table->string('created_by')->nullable();	
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_connection_invoice_d_b_s');
    }
};
