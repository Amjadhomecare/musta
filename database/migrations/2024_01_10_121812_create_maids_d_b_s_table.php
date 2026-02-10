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
        Schema::create('maids_d_b_s', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('uae_id_maid')->nullable();
            $table->integer('salary')->require();
            $table->string('maid_status')->default('pending')->comment('pending,approved,hired,ranaway,visit_visa,terminated,resigned,direct_hire');
            $table->string('maid_type')->nullable();
            $table->string('payment')->default('cash')->comment('cash,bank');
            $table->string('maid_booked')->nullable();
            $table->string('visa_status')->default('without_visa')->comment('without_visa, change_status, with_visa');
            $table->string('note')->default('No data')->nullable();
            $table->string('attachment')->default('No data')->nullable();
            $table->string('agency')->require();
            $table->string('img')->default('No data')->nullable();
            $table->string('img2')->default('No data')->nullable();
            $table->string('video_link')->nullable();
            $table->string('nationality')->require();
            $table->string('religion')->default('No data')->nullable();
            $table->integer('age')->require();  
            $table->string('marital_status')->default('No data')->nullable();
            $table->string('children')->default('No data')->nullable();
            $table->string('education')->default('No data')->nullable();
            $table->string('height')->default('No data')->nullable();
            $table->string('weight')->default('No data')->nullable();
            $table->string('lang_english')->default('beginner')->comment('beginner, intermediate, fluent');
            $table->string('lang_arabic')->default('beginner')->comment('beginner, intermediate, fluent');
            $table->string('cooking')->default('basic')->comment('basic, intermediate, advance');
            $table->string('assist_in_kitchen')->default('No data')->nullable();
            $table->string('baby_sitting')->default('No data')->nullable();
            $table->string('washing')->default('No data')->nullable();
            $table->string('cleaning')->default('No data')->nullable();
            $table->string('passport_number')->default('No data')->nullable();
            $table->date('passport_exp_date')->nullable();
            $table->string('period_country')->nullable();
            $table->string('exp_country')->default('no exp')->nullable();
            $table->date('visit_visa_expired')->nullable();
            $table->string('child')->default('0')->nullable();
            $table->string('animal')->nullable();
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
        Schema::dropIfExists('maids_d_b_s');
    }
};
