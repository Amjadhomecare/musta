<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 0) Normalize invalid zero-dates (strict mode safe) ─────────────────────
        DB::table('upcoming_installments')
            ->where('created_at', '0000-00-00 00:00:00')
            ->update(['created_at' => null]);

        DB::table('upcoming_installments')
            ->where('updated_at', '0000-00-00 00:00:00')
            ->update(['updated_at' => null]);

        // Ensure timestamps accept NULL (no doctrine/dbal required)
        DB::statement("ALTER TABLE upcoming_installments MODIFY created_at TIMESTAMP NULL DEFAULT NULL");
        DB::statement("ALTER TABLE upcoming_installments MODIFY updated_at TIMESTAMP NULL DEFAULT NULL");

        // ── 1) Add customer_id (nullable for backfill) with FK ─────────────────────
        Schema::table('upcoming_installments', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('customer') // keep order near legacy column
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->nullOnDelete();   // if customer deleted, keep row but null FK
        });

        // ── 2) Backfill: match old name to customers.name ──────────────────────────
        // Assumes `customers.name` is unique or at least uniquely maps your data.
        DB::statement("
            UPDATE upcoming_installments ui
            JOIN customers c ON ui.customer = c.name
            SET ui.customer_id = c.id
        ");

        // ── 3) Drop the legacy 'customer' string column ────────────────────────────
        Schema::table('upcoming_installments', function (Blueprint $table) {
            $table->dropColumn('customer');
        });
    }

    public function down(): void
    {
        // ── A) Restore the legacy string column ────────────────────────────────────
        Schema::table('upcoming_installments', function (Blueprint $table) {
            // put it after accrued_date to keep a sensible order on rollback
            $table->string('customer')->nullable()->after('accrued_date');
        });

        // ── B) Best-effort backfill of names from FK ──────────────────────────────
        DB::statement("
            UPDATE upcoming_installments ui
            JOIN customers c ON ui.customer_id = c.id
            SET ui.customer = c.name
            WHERE ui.customer_id IS NOT NULL
        ");

        // ── C) Drop FK & customer_id column ───────────────────────────────────────
        Schema::table('upcoming_installments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        // (Optional) You can revert timestamps to NOT NULL if that was your original schema.
        // Leaving them NULL-safe is usually fine; uncomment if you truly need NOT NULL:
        //
        // DB::statement("ALTER TABLE upcoming_installments MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        // DB::statement("ALTER TABLE upcoming_installments MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
};
