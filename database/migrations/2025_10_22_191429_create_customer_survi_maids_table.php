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
        Schema::create('customer_survi_maids', function (Blueprint $table) {
            $table->id();
                      // FK to maids_d_b_s.id
            $table->foreignId('maid_id')
                ->constrained('maids_d_b_s')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

               $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();    

            /**
             * Satisfaction (رضا العميل)
             * 5 = Very Satisfied (راضي جداً)
             * 4 = Satisfied (راضي)
             * 3 = Neutral (وسط)
             * 2 = Dissatisfied (غير راضي)
             * 1 = Very Dissatisfied (غير راضي مطلقاً)
             */
            $table->unsignedTinyInteger('satisfied')->nullable()->comment(
                '5=Very Satisfied,4=Satisfied,3=Neutral,2=Dissatisfied,1=Very Dissatisfied'
            );

            /**
             * Per-area performance (0–5 scale)
             * 5 = Excellent (ممتاز)
             * 4 = Good (جيد)
             * 3 = Fair (مقبول)
             * 2 = Poor (سيء)
             * 0 = Not Applicable (لا يوجد)
             */
            $table->unsignedTinyInteger('perf_cleaning')->nullable()->comment('5=Excellent,4=Good,3=Fair,2=Poor,0=N/A');
            $table->unsignedTinyInteger('perf_cooking')->nullable()->comment('5=Excellent,4=Good,3=Fair,2=Poor,0=N/A');
            $table->unsignedTinyInteger('perf_childcare')->nullable()->comment('5=Excellent,4=Good,3=Fair,2=Poor,0=N/A');
            $table->unsignedTinyInteger('perf_communication')->nullable()->comment('5=Excellent,4=Good,3=Fair,2=Poor,0=N/A');

            // Free-text note
            $table->text('note')->nullable();

            $table->timestamps();
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_survi_maids');
    }
};
