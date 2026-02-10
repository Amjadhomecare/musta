<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('maid_id')
                ->nullable()
                ->constrained('maids_d_b_s')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);   
            $table->tinyInteger('transaction');     
            $table->tinyInteger('payment_method');  
            $table->tinyInteger('status');      

            $table->string('attachment')->nullable(); 
            $table->string('note')->nullable();     
            $table->json('meta')->nullable();        

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
