<?php

// database/migrations/2025_08_08_165034_update_general_journal_vouchers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1) Allow NULLs so we can replace zero-dates with NULL
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            $table->date('date')->nullable()->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        // 2) Relax sql_mode for this session to allow selecting/updating zero dates
        DB::statement("SET @OLD_SQL_MODE := @@SESSION.sql_mode");
        DB::statement("
            SET SESSION sql_mode = REPLACE(
                REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''),
                'NO_ZERO_IN_DATE', ''
            )
        ");

        // 3) Convert zero dates to NULL (use exact literals per column type)
        //    DATE column:
        DB::statement("
            UPDATE general_journal_vouchers
            SET `date` = NULL
            WHERE `date` = '0000-00-00'
        ");

        //    TIMESTAMP/DATETIME columns:
        DB::statement("
            UPDATE general_journal_vouchers
            SET `created_at` = NULL
            WHERE `created_at` = '0000-00-00 00:00:00'
        ");

        DB::statement("
            UPDATE general_journal_vouchers
            SET `updated_at` = NULL
            WHERE `updated_at` = '0000-00-00 00:00:00'
        ");

        // 4) Restore the original sql_mode
        DB::statement("SET SESSION sql_mode = @OLD_SQL_MODE");

        // 5) Your schema changes
        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            $table->string('voucher_type', 25)->change();
            $table->string('type', 10)->change();
            $table->string('account', 150)->change();
            $table->string('refCode', 30)->change();

            $table->string('receiveRef', 20)->nullable()->default('No Data')->change();
            $table->string('creditNoteRef', 20)->nullable()->default('No Data')->change();
            $table->string('contract_ref', 20)->nullable()->default('No ref')->change();

            $table->string('maid_name', 100)->nullable()->default(null)->change();

            if (!Schema::hasColumn('general_journal_vouchers', 'maid_id')) {
                $table->foreignId('maid_id')
                    ->nullable()
                    ->after('maid_name')
                    ->constrained('maids_d_b_s')
                    ->nullOnDelete();
            }
        });

        // 6) Backfill maid_id from maid_name (idempotent)
        DB::statement("
            UPDATE general_journal_vouchers gjv
            JOIN maids_d_b_s m ON gjv.maid_name = m.name
            SET gjv.maid_id = m.id
            WHERE gjv.maid_id IS NULL
              AND gjv.maid_name IS NOT NULL
              AND gjv.maid_name <> ''
        ");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('general_journal_vouchers', function (Blueprint $table) {
            $table->string('voucher_type', 255)->change();
            $table->string('type', 255)->change();
            $table->string('account', 255)->change();
            $table->string('refCode', 255)->change();

            $table->string('receiveRef', 255)->nullable()->default('No Data')->change();
            $table->string('creditNoteRef', 255)->nullable()->default('No Data')->change();
            $table->string('contract_ref', 255)->nullable()->default('No ref')->change();

            $table->string('maid_name', 255)->nullable()->default('No Maid')->change();

            if (Schema::hasColumn('general_journal_vouchers', 'maid_id')) {
                $table->dropForeign(['maid_id']);
                $table->dropColumn('maid_id');
            }
        });

        Schema::enableForeignKeyConstraints();
    }
};
