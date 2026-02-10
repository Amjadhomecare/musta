<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get current session SQL mode
        $row = DB::selectOne("SELECT @@SESSION.sql_mode AS m");
        $currentSqlMode = $row && isset($row->m) ? (string)$row->m : '';

        // Relax SQL mode by removing the zero-date blockers
        $modes = array_filter(array_map('trim', explode(',', $currentSqlMode)));
        $modes = array_values(array_filter($modes, function ($m) {
            return !in_array(strtoupper($m), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']);
        }));
        $relaxedSqlMode = implode(',', $modes);

        // Apply relaxed mode for this session
        DB::statement("SET SESSION sql_mode = '" . addslashes($relaxedSqlMode) . "'");

        try {
            // 1) Make timestamps nullable so NULL is allowed
            DB::statement("
                ALTER TABLE `customers`
                    MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL,
                    MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL
            ");

            // 2) Convert zero-dates to NULL (use IGNORE for extra safety)
            DB::statement("
                UPDATE IGNORE `customers`
                SET `created_at` = NULL
                WHERE `created_at` = '0000-00-00 00:00:00'
            ");

            DB::statement("
                UPDATE IGNORE `customers`
                SET `updated_at` = NULL
                WHERE `updated_at` = '0000-00-00 00:00:00'
            ");

            // 3) Add ledger_id FK (nullable)
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('ledger_id')
                    ->nullable()
                    ->after('name')
                    ->constrained('all_account_ledger__d_b_s')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete(); // or ->nullOnDelete() if that's preferable
            });

            // 4) Backfill FK by matching customer.name -> ledger.ledger
            DB::statement("
                UPDATE customers c
                JOIN all_account_ledger__d_b_s l
                  ON c.name = l.ledger
                SET c.ledger_id = l.id
            ");
        } finally {
            // Always restore the original SQL mode
            DB::statement("SET SESSION sql_mode = '" . addslashes($currentSqlMode) . "'");
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['ledger_id']);
            $table->dropColumn('ledger_id');
        });
    }
};
