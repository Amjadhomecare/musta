<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add maid_id (nullable for backfill)
        Schema::table('maid_attachments', function (Blueprint $table) {
            $table->foreignId('maid_id')
                ->nullable()
                ->after('id')
                ->constrained('maids_d_b_s')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // 2) Backfill maid_id from existing maid_name
        DB::statement("
            UPDATE maid_attachments ma
            JOIN maids_d_b_s m ON ma.maid_name = m.name
            SET ma.maid_id = m.id
        ");

        // 3) Drop maid_name after backfill
        Schema::table('maid_attachments', function (Blueprint $table) {
            $table->dropColumn('maid_name');
        });

        // (Optional) If you want maid_id NOT NULL after backfill, uncomment below
        // Note: requires doctrine/dbal if your DB needs column modification support.
        /*
        Schema::table('maid_attachments', function (Blueprint $table) {
            $table->foreignId('maid_id')
                ->constrained('maids_d_b_s')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->nullable(false)
                ->change();
        });
        */
    }

    public function down(): void
    {
        // 1) Re-add maid_name
        Schema::table('maid_attachments', function (Blueprint $table) {
            $table->string('maid_name')->nullable()->after('id');
        });

        // 2) Backfill maid_name from maid_id
        DB::statement("
            UPDATE maid_attachments ma
            JOIN maids_d_b_s m ON ma.maid_id = m.id
            SET ma.maid_name = m.name
        ");

        // 3) Drop maid_id FK + column
        Schema::table('maid_attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('maid_id'); // drops FK and column in modern Laravel
        });
    }
};
