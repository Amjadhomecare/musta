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
        Schema::create('maid_doc_expiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maid_id')->constrained('maids_d_b_s')->onDelete('cascade');
            $table->date('labor_card_expiry')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->date('visa_expiry')->nullable();
            $table->date('eid_expiry')->nullable();
            $table->string('created_by')->nullable();	
            $table->string('updated_by')->nullable();
            $table->timestamps();


            $table->unique('maid_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maid_doc_expiries');
   
    }
};
