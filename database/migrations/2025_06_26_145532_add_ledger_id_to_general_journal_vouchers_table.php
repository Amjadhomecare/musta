<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    if (!Schema::hasColumn('general_journal_vouchers', 'ledger_id')) {
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            $table->foreignId('ledger_id')
                ->nullable()
                ->after('account')
                ->constrained('all_account_ledger__d_b_s')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

                DB::statement("
                UPDATE general_journal_vouchers jv
                JOIN all_account_ledger__d_b_s l
                  ON TRIM(LOWER(jv.account)) = TRIM(LOWER(l.ledger))
                SET jv.ledger_id = l.id
            ");
    }
}

public function down()
{
    Schema::table('general_journal_vouchers', function (Blueprint $table) {
        $table->dropForeign(['ledger_id']);
        $table->dropColumn('ledger_id');
    });
}

};
