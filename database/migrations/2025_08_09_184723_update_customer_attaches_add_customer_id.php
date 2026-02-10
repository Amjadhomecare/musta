<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('customer_attaches', function (Blueprint $table) {
            $table->foreignId('customer_id')
                  ->nullable()
                  ->after('customer_name')
                  ->constrained('customers')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // Backfill from customer_name
        DB::statement("
            UPDATE customer_attaches ca
            JOIN customers cu ON ca.customer_name = cu.name
            SET ca.customer_id = cu.id
        ");
    }

    public function down(): void
    {
        Schema::table('customer_attaches', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
