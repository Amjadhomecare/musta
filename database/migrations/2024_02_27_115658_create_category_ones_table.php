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
        Schema::create('category_ones', function (Blueprint $table) {
            $table->id();
            $table->string('customer')->require();
            $table->string('maid')->require();
            $table->string('nationality')->require();
            $table->date('started_date');
            $table->date('ended_date');	
            $table->string('contract_ref')->require();	
            $table->string('invoice_ref')->require();
            $table->integer('amount')->require();     	
            $table->string('category')->require();	
            $table->string('note')->default("No note")->nullable();	  
            $table->string('signature')->default('No signature')->nullable();    
            $table->string('maid_passport')->default('not received')->nullable();   
            $table->string('found_by')->nullable();
            $table->string('extra')->nullable();                                      
            $table->tinyInteger('contract_status')->default('1')->comment('0=disactive,1=active,3=discontinued');	
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
        Schema::dropIfExists('category_ones');
    }
};
