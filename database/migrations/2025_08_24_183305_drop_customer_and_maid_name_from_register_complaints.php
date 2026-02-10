<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('register_complaints', function (Blueprint $table) {
            if (Schema::hasColumn('register_complaints', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('register_complaints', 'maid_name')) {
                $table->dropColumn('maid_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('register_complaints', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->string('maid_name')->nullable();
        });
    }
};
