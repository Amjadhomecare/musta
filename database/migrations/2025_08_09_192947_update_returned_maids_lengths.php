<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
public function up(): void
{

    // Drop index for maid_return_name if it exists
    Schema::table('returned_maids', function (Blueprint $table) {
        $table->dropIndex(['maid_return_name']); // drops index created by $table->index('maid_return_name');
        // Or if it was named differently:
        // $table->dropIndex('returned_maids_maid_return_name_index');
    });

    // Add new columns
    Schema::table('returned_maids', function (Blueprint $table) {
        $table->string('contract', 30)->change();
        $table->string('packagetype', 30)->change();

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

    // Backfill maid_id
    DB::statement("
        UPDATE returned_maids rm
        JOIN maids_d_b_s m ON rm.maid_return_name = m.name
        SET rm.maid_id = m.id
    ");

    // Backfill customer_id
    DB::statement("
        UPDATE returned_maids rm
        JOIN customers c ON rm.customer = c.name
        SET rm.customer_id = c.id
    ");
}

};
