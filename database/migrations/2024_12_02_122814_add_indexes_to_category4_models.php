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
        Schema::table('category4_models', function (Blueprint $table) {
            $table->index('maid'); 
            $table->index('date'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category4_models', function (Blueprint $table) {
            $table->dropIndex(['maid']); 
            $table->dropIndex(['date']); 
        });
    }
};
