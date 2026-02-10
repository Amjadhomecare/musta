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
        Schema::create('apply_visas', function (Blueprint $table) {
            $table->id();
              $table->date('date')->nullable();

       
            $table->foreignId('maid_id')
                ->constrained('maids_d_b_s')   
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // 0=visa renewal, 2=new visa, 3=cancellation, 4=absconding, 5=other
            $table->tinyInteger('service')->index()
                ->comment('0=visa renewal, 2=new visa, 3=cancellation, 4=absconding, 5=other');

            // JSON of files/keys stored in R2
            $table->json('document')->nullable()
                ->comment('Documents uploaded in R2');

            $table->string('note')->nullable();

            // 0=created, 1=pending, 2=rejected, 3=approved, 4=missing document
            $table->tinyInteger('status')->default(0)->index()
                ->comment('0=created, 1=pending, 2=rejected, 3=approved, 4=missing document');

            // keep your exact column name
            $table->tinyInteger('managment_approval')->default(0)->index()
                ->comment('0=pending, 1=approved');

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
        Schema::dropIfExists('apply_visas');
    }
};
