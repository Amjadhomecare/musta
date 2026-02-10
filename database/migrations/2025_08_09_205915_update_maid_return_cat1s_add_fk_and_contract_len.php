<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
   
        Schema::table('maid_return_cat1s', function (Blueprint $table) {
            // 1) contract -> VARCHAR(30)
            $table->string('contract', 30)->change();

            // 2) Add foreign keys (nullable so we can backfill first)
            $table->foreignId('maid_id')
                  ->nullable()
                  ->after('maid_return_name')
                  ->constrained('maids_d_b_s')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId('customer_id')
                  ->nullable()
                  ->after('customer')
                  ->constrained('customers')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // 3) Backfill from existing string columns
        DB::statement("
            UPDATE maid_return_cat1s mr
            JOIN maids_d_b_s m ON mr.maid_return_name = m.name
            SET mr.maid_id = m.id
        ");

        DB::statement("
            UPDATE maid_return_cat1s mr
            JOIN customers c ON mr.customer = c.name
            SET mr.customer_id = c.id
        ");
    }

    public function down(): void
    {
        Schema::table('maid_return_cat1s', function (Blueprint $table) {
            $table->dropForeign(['maid_id']);
            $table->dropColumn('maid_id');

            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            $table->string('contract')->change(); // revert length
        });
    }
};
