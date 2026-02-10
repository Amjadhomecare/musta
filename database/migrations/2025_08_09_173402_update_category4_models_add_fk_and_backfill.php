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

        // 1) Make timestamps nullable so we can set NULL
        Schema::table('category4_models', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        // 2) Relax sql_mode for THIS SESSION so zero-dates can be filtered & updated
        DB::statement("SET @OLD_SQL_MODE := @@SESSION.sql_mode");
        DB::statement("
            SET SESSION sql_mode = REPLACE(
                REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''),
                'NO_ZERO_IN_DATE', ''
            )
        ");

        // 3) Normalize zero datetimes to NULL
        DB::statement("
            UPDATE category4_models
            SET created_at = NULL
            WHERE created_at = '0000-00-00 00:00:00'
        ");
        DB::statement("
            UPDATE category4_models
            SET updated_at = NULL
            WHERE updated_at = '0000-00-00 00:00:00'
        ");

        // 4) Restore original sql_mode
        DB::statement("SET SESSION sql_mode = @OLD_SQL_MODE");

        // 5) Your schema changes + FKs
        Schema::table('category4_models', function (Blueprint $table) {
            $table->string('Contract_ref', 30)->change();
            $table->string('category', 30)->change();

            if (!Schema::hasColumn('category4_models', 'customer_id')) {
                $table->foreignId('customer_id')
                      ->nullable()
                      ->after('customer')
                      ->constrained('customers')
                      ->cascadeOnUpdate()
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('category4_models', 'maid_id')) {
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
            UPDATE category4_models c4
            JOIN customers cu ON c4.customer = cu.name
            SET c4.customer_id = cu.id
            WHERE c4.customer_id IS NULL
        ");

        DB::statement("
            UPDATE category4_models c4
            JOIN maids_d_b_s m ON c4.maid = m.name
            SET c4.maid_id = m.id
            WHERE c4.maid_id IS NULL
        ");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('category4_models', function (Blueprint $table) {
            $table->string('Contract_ref')->change();
            $table->string('category')->change();

            if (Schema::hasColumn('category4_models', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }

            if (Schema::hasColumn('category4_models', 'maid_id')) {
                $table->dropForeign(['maid_id']);
                $table->dropColumn('maid_id');
            }
        });

        Schema::enableForeignKeyConstraints();
    }
};
