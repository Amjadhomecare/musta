<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $ledgersTable = 'all_account_ledger__d_b_s'; // id, ledger
    private string $maidsTable   = 'maids_d_b_s';               // id, name

    public function up(): void
    {
        // 1) Ensure FK columns exist (nullable for backfill)
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('general_journal_vouchers', 'ledger_id')) {
                $table->unsignedBigInteger('ledger_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('general_journal_vouchers', 'maid_id')) {
                $table->unsignedBigInteger('maid_id')->nullable()->after('ledger_id');
            }
        });

        // 2) Backfill with EXACT match only
        // Ledger
        DB::statement("
            UPDATE general_journal_vouchers gjv
            LEFT JOIN {$this->ledgersTable} l
              ON l.ledger = gjv.account
            SET gjv.ledger_id = l.id
            WHERE gjv.ledger_id IS NULL
              AND gjv.account IS NOT NULL
              AND gjv.account <> ''
        ");

        // Maid
        DB::statement("
            UPDATE general_journal_vouchers gjv
            LEFT JOIN {$this->maidsTable} m
              ON m.name = gjv.maid_name
            SET gjv.maid_id = m.id
            WHERE gjv.maid_id IS NULL
              AND gjv.maid_name IS NOT NULL
              AND gjv.maid_name <> ''
        ");

      
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('general_journal_vouchers', 'maid_name')) {
                $table->dropColumn('maid_name');
            }
            if (Schema::hasColumn('general_journal_vouchers', 'account')) {
                $table->dropColumn('account');
            }
        });
        
    }

    public function down(): void
    {
        // Recreate text columns
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('general_journal_vouchers', 'maid_name')) {
                $table->string('maid_name')->nullable()->after('maid_id');
            }
            if (!Schema::hasColumn('general_journal_vouchers', 'account')) {
                $table->string('account')->nullable()->after('maid_name');
            }
        });

        // Restore from IDs (exact join back)
        DB::statement("
            UPDATE general_journal_vouchers gjv
            LEFT JOIN {$this->ledgersTable} l ON l.id = gjv.ledger_id
            SET gjv.account = l.ledger
            WHERE gjv.ledger_id IS NOT NULL
        ");

        DB::statement("
            UPDATE general_journal_vouchers gjv
            LEFT JOIN {$this->maidsTable} m ON m.id = gjv.maid_id
            SET gjv.maid_name = m.name
            WHERE gjv.maid_id IS NOT NULL
        ");

      
    }
};
