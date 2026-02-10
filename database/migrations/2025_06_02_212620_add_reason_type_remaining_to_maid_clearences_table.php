<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('maid_clearences', function (Blueprint $table) {
        $table->enum('reason', ['renewal', 'cancel'])->nullable()->after('maid_name');
        $table->enum('type', ['maid', 'staff'])->nullable()->after('reason');
        $table->integer('remaining_amount')->nullable()->after('type');
    });
}

public function down(): void
{
    Schema::table('maid_clearences', function (Blueprint $table) {
        $table->dropColumn(['reason', 'type', 'remaining_amount']);
    });
}
};
