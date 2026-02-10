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
        Schema::table('direct_debits', function (Blueprint $table) {
               $table->tinyInteger('status')->default(0)->after('payment_frequency')->comment('Active or not');
                $table->tinyInteger('active')->default(0)->after('status');
                $table->tinyInteger('branch')->default(0)->after('active')->comment('hc = 0 , fc = 1');
                $table->string('note')->nullable()->after('branch')->comment('Note for the direct debit');
                $table->string('rejected_reason')->nullable()->after('note');
                $table->string('center_bank_ref')->nullable()->after('rejected_reason');
                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_debits', function (Blueprint $table) {
            $table->dropColumn(['status', 'active', 'note' , 'branch', 'rejected_reason' , 'center_bank_ref']);
        });
    }
};
