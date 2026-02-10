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
        Schema::create('home_care_blogs_ars', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique('service_pages_slug_unique');
            $table->json('content');
            $table->string('status', 255)->default('draft');
            $table->boolean('enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->json('hero_section')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_care_blogs_ars');
    }
};
