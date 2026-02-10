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
        Schema::create('training_invs', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('maid_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('ref')->nullable();
            $table->string('branch')->nullable();
            $table->json('extra')->nullable();
            $table->string('status')->default('unpaid');
            $table->decimal('amount_paid')->nullable();
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
        Schema::dropIfExists('training_invs');
    }
};
