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
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');  
            $table->foreignId('maid_id')->constrained('maids_d_b_s')->onDelete('cascade');   
            $table->string('reason')->require();
            $table->string('note')->require();
            $table->string('status')->default("Pending")->require();
            $table->string('assigned_to')->nullable();
            $table->string('forward_to')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
    }
};
