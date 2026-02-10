<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maid_return_cat1s', function (Blueprint $table) {
            $table->dropColumn(['maid_return_name', 'customer']);
        });
    }

    public function down(): void
    {
        Schema::table('maid_return_cat1s', function (Blueprint $table) {
            $table->string('maid_return_name')->nullable(false);
            $table->string('customer')->nullable(false);
        });
    }
};
