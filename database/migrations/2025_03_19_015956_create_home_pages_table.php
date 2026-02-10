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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->string('id')->primary()->default('home_page');
            $table->json('metadata')->nullable()->comment('SEO metadata including title, description, Open Graph, and Twitter details');
            $table->json('hero_section')->nullable()->comment('Hero section content including title, description, and images');
            $table->json('visa_section')->nullable()->comment('Visa section details including features');
            $table->json('hire_maid_section')->nullable()->comment('Hire maid section details including features');
            $table->json('direct_sponsorship_section')->nullable()->comment('Direct sponsorship section details including features');
            $table->json('about_section')->nullable()->comment('About section including intro and multiple sections');
            $table->json('reviews_section')->nullable()->comment('List of reviews including reviewer name, rating, and comment');
            $table->json('qa_section')->nullable()->comment('List of Q&A including question and answer pairs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
