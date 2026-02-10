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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
               // S3 URLs
            $table->string('customer_signature_url');
            $table->string('staff_signature_url');

            // optional meta-data
            $table->string('customer_name')->nullable();
            $table->string('maid_name')->nullable();
            $table->string('note')->nullable();
            $table->string('for')->nullable();

            // workflow flag
            $table->boolean('checked')->default(false);

            // now string, not bigint
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
        Schema::dropIfExists('signatures');
    }
};
