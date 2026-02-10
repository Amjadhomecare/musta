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
        Schema::create('ticket_maids', function (Blueprint $table) {
            $table->id();
            $table->string('maid_name');
            $table->date('travel_date');
            $table->string('destination');
            $table->date('return_date')->nullable();
            $table->enum ('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('ticket_number')->nullable();
            $table->string('ticket_type')->nullable();
            $table->string('ticket_price')->nullable();  
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
        Schema::dropIfExists('ticket_maids');
    }
};
