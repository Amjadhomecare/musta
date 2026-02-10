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
        Schema::create('customer_advances', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('customer')->nullable();
            $table->string('phone')->nullable();
            $table->string('maid')->nullable();
            $table->string('ref')->nullable();
            $table->string('post_type')->nullable();
            $table->string('note')->nullable();
            $table->string('received')->nullable();
            $table->integer('amount');               
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
        Schema::dropIfExists('customer_advances');
    }
};
