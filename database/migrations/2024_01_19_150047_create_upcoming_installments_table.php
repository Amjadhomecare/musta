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
        Schema::create('upcoming_installments', function (Blueprint $table) {
            $table->id();
            $table->date('accrued_date')->require();
            $table->string('customer')->require();
            $table->string('note')->default("No note")->nullable();
            $table->string('cheque')->default("No cheque")->nullable();
            $table->string('contract')->require();
            $table->integer('amount')->require();
            $table->tinyInteger('invoice_status')->default('0')->comment('0=pending, 1=generated');
            $table->string('invoice')->default('No invoice')->nullable();
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
        Schema::dropIfExists('upcoming_installments');
    }
};
