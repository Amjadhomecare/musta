<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Add maid_id column
        Schema::table('pay_maid_payrolls', function (Blueprint $table) {
            $table->foreignId('maid_id')
                  ->nullable()
                  ->after('maid')
                  ->constrained('maids_d_b_s')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // 2️⃣ Backfill maid_id from existing maid name
        DB::statement("
            UPDATE pay_maid_payrolls p
            JOIN maids_d_b_s m ON p.maid = m.name
            SET p.maid_id = m.id
        ");

        // 3️⃣ Drop the old maid column
        Schema::table('pay_maid_payrolls', function (Blueprint $table) {
            $table->dropColumn('maid');
        });
    }

    public function down(): void
    {
        // Reverse the process
        Schema::table('pay_maid_payrolls', function (Blueprint $table) {
            $table->string('maid')->nullable()->after('accrued_month');
        });

        DB::statement("
            UPDATE pay_maid_payrolls p
            JOIN maids_d_b_s m ON p.maid_id = m.id
            SET p.maid = m.name
        ");

        Schema::table('pay_maid_payrolls', function (Blueprint $table) {
            $table->dropForeign(['maid_id']);
            $table->dropColumn('maid_id');
        });
    }
};
