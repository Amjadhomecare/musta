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
        Schema::create('cancelation_dds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dd_id')->constrained('direct_debits');
            $table->tinyInteger('task');
            $table->tinyInteger('status');
            $table->string('note')->nullable();
            $table->json('meta')->nullable();
            $table->json('comment')->nullable();
            $table->foreignId('created_by')->constrained('erp_users');
            $table->foreignId('update_by')->constrained('erp_users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancelation_dds');
    }
};
