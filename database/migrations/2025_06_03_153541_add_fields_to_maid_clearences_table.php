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
        Schema::table('maid_clearences', function (Blueprint $table) {
            $table->string('nationality')->nullable();
            $table->string('pp')->nullable();
            $table->date('pp_expire')->nullable();
            $table->string('emirate_id')->nullable();
            $table->string('job_title')->nullable();
            $table->integer('basic_salary')->nullable();
            $table->integer('salary_dh')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maid_clearences', function (Blueprint $table) {
             $table->dropColumn([
                'nationality',
                'pp',
                'pp_expire',
                'emirate_id',
                'basic_salary',
                'salary_dh',
                'job_title',
            ]);
        });
    }
};
