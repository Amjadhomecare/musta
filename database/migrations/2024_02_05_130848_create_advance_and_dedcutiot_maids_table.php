<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AdvanceAndDedcutiotMaid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advance_and_dedcutiot_maids', function (Blueprint $table) {
            $table->id();
            $table->date('date')->require();
 
            $table->string('maid')->require();
            $table->string('note')->require();
            $table->integer('deduction')->default(0)->nullable();
            $table->integer('Allowance')->default(0)->nullable();
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
        Schema::dropIfExists('advance_and_dedcutiot_maids');
    }
};
