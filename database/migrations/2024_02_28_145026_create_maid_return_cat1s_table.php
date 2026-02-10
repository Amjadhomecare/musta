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
        Schema::create('maid_return_cat1s', function (Blueprint $table) {
            $table->id();
            $table->date('returned_date')->require();
            $table->string('packagetype')->require();
            $table->string('maid_return_name')->require();
            $table->string('contract')->require();
            $table->string('customer')->require();
            $table->string('reason')->require();
            $table->string('latest_invoce')->nullable();
            $table->string('approval')->default('no')->nullable();
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
        Schema::dropIfExists('maid_return_cat1s');
    }
};
