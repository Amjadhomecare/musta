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
        Schema::create('dd_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dd_id')->constrained('direct_debits')->cascadeOnDelete();
            $table->integer('attempt_number')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->tinyInteger('follow_up_status')->nullable()->comment('1 Followed Up sent, 2 Customer did not reply, 3 Customer replied, 4 Follow up manually');
            $table->json('message_sent')->nullable();
            $table->json('attachment')->nullable()->comment("Example: {sign: 'url', sign2: 'url',paper_sign: 'url'}");
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
        Schema::dropIfExists('dd_follow_ups');
    }
};
