<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1) Allow NULLs on timestamps so we can null out zero-datetimes
        Schema::table('category_ones', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        // 2) Relax sql_mode for THIS SESSION so WHERE/UPDATE on zero-dates won’t throw 1292
        DB::statement("SET @OLD_SQL_MODE := @@SESSION.sql_mode");
        DB::statement("
            SET SESSION sql_mode = REPLACE(
                REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''),
                'NO_ZERO_IN_DATE', ''
            )
        ");

        // 3) Normalize zero datetimes to NULL
        DB::statement("
            UPDATE category_ones
            SET created_at = NULL
            WHERE created_at = '0000-00-00 00:00:00'
        ");
        DB::statement("
            UPDATE category_ones
            SET updated_at = NULL
            WHERE updated_at = '0000-00-00 00:00:00'
        ");

        // 4) Restore original sql_mode
        DB::statement("SET SESSION sql_mode = @OLD_SQL_MODE");

        // 5) Your schema changes + FKs
        Schema::table('category_ones', function (Blueprint $table) {
            // Length changes
            $table->string('contract_ref', 30)->change();
            $table->string('invoice_ref', 30)->change();

            // Add foreign keys (nullable, safe to re-run with hasColumn checks)
            if (!Schema::hasColumn('category_ones', 'customer_id')) {
                $table->foreignId('customer_id')
                      ->nullable()
                      ->after('customer')
                      ->constrained('customers')
                      ->cascadeOnUpdate()
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('category_ones', 'maid_id')) {
                $table->foreignId('maid_id')
                      ->nullable()
                      ->after('maid')
                      ->constrained('maids_d_b_s')
                      ->cascadeOnUpdate()
                      ->nullOnDelete();
            }
        });

        // 6) Backfill FKs (idempotent)
        DB::statement("
            UPDATE category_ones c1
            JOIN customers cu ON c1.customer = cu.name
            SET c1.customer_id = cu.id
            WHERE c1.customer_id IS NULL
        ");

        DB::statement("
            UPDATE category_ones c1
            JOIN maids_d_b_s m ON c1.maid = m.name
            SET c1.maid_id = m.id
            WHERE c1.maid_id IS NULL
        ");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('category_ones', function (Blueprint $table) {
            // Revert string sizes
            $table->string('contract_ref')->change();
            $table->string('invoice_ref')->change();

            // Drop FKs/columns if present
            if (Schema::hasColumn('category_ones', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('category_ones', 'maid_id')) {
                $table->dropForeign(['maid_id']);
                $table->dropColumn('maid_id');
            }

            // (Optional) If you want to force NOT NULL back on timestamps, uncomment:
            // $table->timestamp('created_at')->nullable(false)->useCurrent()->change();
            // $table->timestamp('updated_at')->nullable(false)->useCurrentOnUpdate()->change();
        });

        Schema::enableForeignKeyConstraints();
    }
};
