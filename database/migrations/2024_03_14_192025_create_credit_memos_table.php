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
        Schema::create('credit_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo_ref')->unique()->require();
            $table->date('date')->require();
            $table->string('contract_ref')->unique()->require();
            $table->string('contract_type')->require();
            $table->string('customer')->require();
            $table->string('maid')->require();
            $table->string('note')->require();
            $table->date('started_date')->require();
            $table->date('returned_date')->require();
            $table->integer('amount_received')->require(); 
            $table->integer('amount_deduction')->require(); 
            $table->integer('amount_for_maid')->require(); 
            $table->integer('refunded_amount')->require();
            $table->tinyInteger('status')->default('0')->comment('0=pending,1=approved,3=disapproved');     
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
        Schema::dropIfExists('credit_memos');
    }
};
