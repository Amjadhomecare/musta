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
        Schema::create('maids_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maid_id')
            ->constrained('maids_d_b_s','id')
            ->onDelete('cascade');
            $table->boolean('has_dog')->default(false);
            $table->boolean('has_cat')->default(false);
            $table->boolean('working_days_off')->default(false);
            $table->string('babysitting')->nullable();
            $table->boolean('private_room')->default(false);
            $table->boolean('elderly_care')->default(false);
            $table->boolean('special_needs_care')->default(false);
            $table->boolean('knows_syrian_lebanese')->default(false);
            $table->boolean('can_assist_and_cook')->default(false);
            $table->boolean('knows_gulf_food')->default(false);
            $table->boolean('international_cooking')->default(false);
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
        Schema::dropIfExists('maids_filters');
    }
};
