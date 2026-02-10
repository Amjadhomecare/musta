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
        Schema::table('advance_and_dedcutiot_maids', function (Blueprint $table) {
            $table->foreignId('maid_id')
                  ->nullable()
                  ->after('maid')
                  ->constrained('maids_d_b_s')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // 2️⃣ Backfill maid_id from existing maid name
        DB::statement("
            UPDATE advance_and_dedcutiot_maids adm
            JOIN maids_d_b_s m ON adm.maid = m.name
            SET adm.maid_id = m.id
        ");

        // 3️⃣ Drop old maid column
        Schema::table('advance_and_dedcutiot_maids', function (Blueprint $table) {
            $table->dropColumn('maid');
        });
    }

    public function down(): void
    {
        // Reverse changes
        Schema::table('advance_and_dedcutiot_maids', function (Blueprint $table) {
            $table->string('maid')->nullable()->after('date');
        });

        DB::statement("
            UPDATE advance_and_dedcutiot_maids adm
            JOIN maids_d_b_s m ON adm.maid_id = m.id
            SET adm.maid = m.name
        ");

        Schema::table('advance_and_dedcutiot_maids', function (Blueprint $table) {
            $table->dropForeign(['maid_id']);
            $table->dropColumn('maid_id');
        });
    }
};
