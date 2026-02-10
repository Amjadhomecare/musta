<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
    
        Schema::table('register_complaints', function (Blueprint $table) {
            // Drop old FKs on name
            $table->dropForeign(['customer_name']);
            $table->dropForeign(['maid_name']);

            // Add IDs
            $table->foreignId('customer_id')
                  ->nullable()
                  ->after('maid_name')
                  ->constrained('customers')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId('maid_id')
                  ->nullable()
                  ->after('customer_id')
                  ->constrained('maids_d_b_s')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // Backfill IDs from names
        DB::statement("
            UPDATE register_complaints rc
            JOIN customers c ON rc.customer_name = c.name
            SET rc.customer_id = c.id
        ");

        DB::statement("
            UPDATE register_complaints rc
            JOIN maids_d_b_s m ON rc.maid_name = m.name
            SET rc.maid_id = m.id
        ");
    }

    public function down(): void
    {
        Schema::table('register_complaints', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['maid_id']);
            $table->dropColumn(['customer_id', 'maid_id']);
        });

        // You could re-add the old foreign keys to names here if needed
    }
};
