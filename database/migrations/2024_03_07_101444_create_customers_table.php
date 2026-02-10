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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->require();
            $table->string('related')->default("No data")->nullable();
            $table->string('note')->default("No data")->nullable();
            $table->string('phone')->unique()->require();
            $table->string('secondaryPhone')->unique()->nullable();
            $table->string('idType')->nullable();
            $table->string('idNumber')->unique()->nullable();
            $table->string('nationality')->nullable();
            $table->string('cusomerType')->nullable();
            $table->string('email')->default("No data")->nullable();
            $table->string('address')->default("No data")->nullable();
            $table->tinyInteger('status')->default('1')->comment('0=black_list, 1=approved , 2=VIP');
            $table->string('idImg')->default('No ID')->nullable();
            $table->string('passportImg')->default('No data')->nullable();
            $table->string('signature')->default('No signature')->nullable();
            $table->string('from_website')->nullable();
            $table->string('created_by')->nullable();	
            $table->string('updated_by')->nullable();
            $table->text('token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
