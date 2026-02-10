<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $collation = 'utf8mb4_0900_ai_ci';

    public function up(): void
    {
        // 0) Normalize table + text columns to a single charset/collation
        DB::statement("
            ALTER TABLE customer_advances
            CONVERT TO CHARACTER SET utf8mb4
            COLLATE {$this->collation}
        ");

        // Ensure the string columns themselves use the same collation
        // (Skip if the columns don't exist in your schema)
        if (Schema::hasColumn('customer_advances', 'customer')) {
            DB::statement("
                ALTER TABLE customer_advances
                MODIFY customer VARCHAR(255)
                CHARACTER SET utf8mb4 COLLATE {$this->collation} NULL
            ");
        }
        if (Schema::hasColumn('customer_advances', 'maid')) {
            DB::statement("
                ALTER TABLE customer_advances
                MODIFY maid VARCHAR(255)
                CHARACTER SET utf8mb4 COLLATE {$this->collation} NULL
            ");
        }

        // 1) Add FK columns if missing
        if (!Schema::hasColumn('customer_advances', 'customer_id')) {
            Schema::table('customer_advances', function (Blueprint $table) {
                $table->foreignId('customer_id')
                      ->nullable()
                      ->after('customer');
            });
            // Add FK if target table exists
            if (Schema::hasTable('customers')) {
                Schema::table('customer_advances', function (Blueprint $table) {
                    $table->foreign('customer_id', 'ca_customer_id_fk')
                          ->references('id')->on('customers')
                          ->cascadeOnUpdate()->nullOnDelete();
                });
            }
        }

        if (!Schema::hasColumn('customer_advances', 'maid_id')) {
            Schema::table('customer_advances', function (Blueprint $table) {
                $table->foreignId('maid_id')
                      ->nullable()
                      ->after('maid');
            });
            // Add FK if target table exists
            if (Schema::hasTable('maids_d_b_s')) {
                Schema::table('customer_advances', function (Blueprint $table) {
                    $table->foreign('maid_id', 'ca_maid_id_fk')
                          ->references('id')->on('maids_d_b_s')
                          ->cascadeOnUpdate()->nullOnDelete();
                });
            }
        }

        // 2) Backfill IDs (run only if columns exist)
        if (Schema::hasColumn('customer_advances', 'customer_id')) {
            DB::statement("
                UPDATE customer_advances ca
                JOIN customers cu
                  ON LOWER(TRIM(CONVERT(ca.customer USING utf8mb4))) COLLATE {$this->collation}
                   = LOWER(TRIM(CONVERT(cu.name     USING utf8mb4))) COLLATE {$this->collation}
                SET ca.customer_id = cu.id
                WHERE ca.customer_id IS NULL
                  AND ca.customer IS NOT NULL
                  AND ca.customer <> ''
            ");
        }

        if (Schema::hasColumn('customer_advances', 'maid_id')) {
            DB::statement("
                UPDATE customer_advances ca
                JOIN maids_d_b_s m
                  ON LOWER(TRIM(CONVERT(ca.maid USING utf8mb4))) COLLATE {$this->collation}
                   = LOWER(TRIM(CONVERT(m.name USING utf8mb4))) COLLATE {$this->collation}
                SET ca.maid_id = m.id
                WHERE ca.maid_id IS NULL
                  AND ca.maid IS NOT NULL
                  AND ca.maid <> ''
            ");
        }
    }

    public function down(): void
    {
        // Safely drop FKs/columns only if they exist
        if (Schema::hasColumn('customer_advances', 'customer_id')) {
            Schema::table('customer_advances', function (Blueprint $table) {
                // FK names must match what we created above
                try { $table->dropForeign('ca_customer_id_fk'); } catch (\Throwable $e) {}
                $table->dropColumn('customer_id');
            });
        }

        if (Schema::hasColumn('customer_advances', 'maid_id')) {
            Schema::table('customer_advances', function (Blueprint $table) {
                try { $table->dropForeign('ca_maid_id_fk'); } catch (\Throwable $e) {}
                $table->dropColumn('maid_id');
            });
        }

        // (Optional) Revert collation if you want:
        // DB::statement("ALTER TABLE customer_advances CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }
};
