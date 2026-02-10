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
        Schema::create('category4_models', function (Blueprint $table) {
            $table->id();
            $table->date('date');	
            $table->string('Contract_ref')->require();	
            $table->string('customer')->require();
            $table->string('maid')->require();		 
            $table->string('category')->require();	
            $table->string('note')->default("No note")->nullable();	                                         
            $table->tinyInteger('contract_status')->default('1')->comment('0=disactive,1=active,3=discontinued');
            $table->string('found_by')->nullable();
            $table->string('from_website')->nullable();
            $table->string('signature')->default('No signature')->nullable();
            $table->string('extra')->nullable();		 	
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
        Schema::dropIfExists('category4_models');
    }
};
