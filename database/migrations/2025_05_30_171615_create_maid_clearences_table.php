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
        Schema::create('maid_clearences', function (Blueprint $table) {
            $table->id();
            $table->string('maid_name');
            $table->date('last_entry_date')->nullable();
            $table->date('travel_date')->nullable();
            $table->decimal('dedcution', 10, 2)->nullable();
            $table->decimal('ticket', 10, 2)->nullable(); 
            $table->decimal('allowance', 10, 2)->nullable();
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maid_clearences');
    }
};
