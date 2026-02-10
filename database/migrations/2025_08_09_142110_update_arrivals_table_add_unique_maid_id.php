<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add maid_id (nullable so we can backfill)
        Schema::table('arrivals', function (Blueprint $table) {
            $table->foreignId('maid_id')
                  ->after('id')
                  ->nullable()
                  ->constrained('maids_d_b_s')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // 2) Backfill maid_id from existing name
        DB::statement("
            UPDATE arrivals a
            JOIN maids_d_b_s m ON a.name = m.name
            SET a.maid_id = m.id
        ");

        // 3) Make maid_id unique & drop the old name column
        Schema::table('arrivals', function (Blueprint $table) {
            $table->unique('maid_id');
            $table->dropUnique(['name']);
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        // 1) Recreate the name column (nullable first so backfill won’t fail)
        Schema::table('arrivals', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        // 2) Backfill name from maid_id
        DB::statement("
            UPDATE arrivals a
            JOIN maids_d_b_s m ON a.maid_id = m.id
            SET a.name = m.name
        ");

        // 3) Restore the unique on name, then drop maid_id + its unique/FK
        Schema::table('arrivals', function (Blueprint $table) {
            $table->unique('name');

            $table->dropForeign(['maid_id']);
            $table->dropUnique(['maid_id']);
            $table->dropColumn('maid_id');
        });
    }
};
