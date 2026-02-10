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
        Schema::create('pay_maid_payrolls', function (Blueprint $table) {
            $table->id();
            $table->date('accrued_month')->require();
            $table->string('maid');
            $table->foreign('maid')->references('name')->on('maids_d_b_s')->onDelete('cascade'); 
            $table->string('status')->nullable();
            $table->string('basic')->nullable();
            $table->string('maid_type')->nullable();
            $table->string('method')->nullable();
            $table->integer('working_dayes')->default(0)->nullable();
            $table->integer('deduction')->default(0)->nullable();
            $table->integer('allowance')->default(0)->nullable();
            $table->string('note')->default("No note")->nullable();
            $table->integer('net_salary')->default(0)->nullable();
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
        Schema::dropIfExists('pay_maid_payrolls');
    }
};
