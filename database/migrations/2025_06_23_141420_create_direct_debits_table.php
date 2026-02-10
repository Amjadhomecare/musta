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
        Schema::create('direct_debits', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->nullable();
            $table->string('payment_frequency')->default('M')->comment('M for Monthly, Q for Quarterly, A for Yearly'); 
            $table->date('commences_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_type')->default('C')->comment('C for Current or Saving, O for credit card');
            $table->string('paying_bank_name')->nullable();
            $table->string('paying_bank_id')->nullable();
            $table->string('customer_type')->nullable()->comment('Individual or Company IN or NI');
            $table->string('customer_id_no')->nullable();
            $table->decimal('fixed_amount', 12, 2)->nullable();
            $table->string('customer_id_type')->nullable()->comment('EIDAC emirate id');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('extra')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
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
        Schema::dropIfExists('direct_debits');
    }
};
