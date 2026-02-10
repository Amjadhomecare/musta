<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add maid_id column
        Schema::table('releases', function (Blueprint $table) {
            $table->foreignId('maid_id')
                ->nullable()
                ->after('id')
                ->constrained('maids_d_b_s')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // Step 2: Backfill maid_id from existing name
        DB::statement("
            UPDATE releases r
            JOIN maids_d_b_s m ON r.name = m.name
            SET r.maid_id = m.id
        ");

        // Step 3: Drop unique index and name column
        Schema::table('releases', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        // Add name back if rolling back
        Schema::table('releases', function (Blueprint $table) {
            $table->string('name')->unique()->nullable();
        });

        DB::statement("
            UPDATE releases r
            JOIN maids_d_b_s m ON r.maid_id = m.id
            SET r.name = m.name
        ");

        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeign(['maid_id']);
            $table->dropColumn('maid_id');
        });
    }
};
