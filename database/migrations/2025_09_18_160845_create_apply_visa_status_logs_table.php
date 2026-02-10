<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apply_visa_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('apply_visa_id')
                ->constrained('apply_visas')
                ->cascadeOnDelete();
            $table->tinyInteger('status')->index()
                ->comment('0=created, 1=pending, 2=rejected, 3=approved, 4=missing document');

            $table->foreignId('maid_id')
                ->constrained('maids_d_b_s')
                ->cascadeOnDelete();
    
            $table->string('created_by')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apply_visa_status_logs');
    }
};
