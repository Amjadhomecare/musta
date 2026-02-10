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
        Schema::create('accounting_pre_connections', function (Blueprint $table) {
            $table->id();
            $table->string('group')->nullable();	
            $table->string('extra')->nullable();
            $table->string('name_of_connection')->require();	
            $table->string('type')->require();
            $table->string('account')->require();	
            $table->decimal('amount',10, 2)->require();	
            $table->string('notes')->nullable();	
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
        Schema::dropIfExists('accounting_pre_connections');
    }
};
