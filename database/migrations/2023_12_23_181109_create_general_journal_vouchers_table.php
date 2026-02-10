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
        Schema::create('general_journal_vouchers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('refCode')->require();
            $table->integer('refNumber');
            $table->string('voucher_type')->require();
            $table->string('type')->require();
            $table->string('pre_connection_name')->default('No connection')->nullable();
            $table->string('maid_name')->default('No Maid')->nullable();
            $table->string('account')->require();
            $table->decimal('amount', 10, 2)->require();
            $table->decimal('invoice_balance', 10, 2)->default(0)->require();
            $table->string('notes')->default('No Data')->nullable();
            $table->string('receiveRef')->default('No Data')->nullable();
            $table->string('creditNoteRef')->default('No Data')->nullable();
            $table->string('contract_ref')->default("No ref")->nullable();	
            $table->string('extra')->nullable();	
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->index('refCode');
            $table->index('account');
            $table->index('voucher_type');
            $table->index('type');
            $table->index('created_at');
            $table->index('date');
            $table->index('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_journal_vouchers');
    }
};
