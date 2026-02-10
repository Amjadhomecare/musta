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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->string('maid_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: pending, 1: success, 2:maid rejected , 3: customer rejected');
            $table->string('type')->nullable();
            $table->string('room')->nullable();
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
        Schema::dropIfExists('interviews');
    }
};
