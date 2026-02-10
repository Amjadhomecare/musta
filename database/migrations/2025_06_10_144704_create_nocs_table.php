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
        Schema::create('nocs', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('maid_name');
            $table->string('note')->nullable();
            $table->date('t_date')->nullable();
            $table->date('r_date')->nullable();
            $table->string('country')->nullable();
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
        Schema::dropIfExists('nocs');
    }
};
