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
        Schema::create('refund_dds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dd_id')->constrained('direct_debits')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_dds');
    }
};
