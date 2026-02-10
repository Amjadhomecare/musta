<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('maids_d_b_s', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('video_id');
        });

        Schema::table('maids_d_b_s', function (Blueprint $table) {
            // Re-add video_id as JSON
            $table->json('video_id')->nullable()->after('name');

            // Add video_link_s after video_id
            $table->string('video_link_s')->nullable()->after('video_id');
        });
    }

    public function down()
    {
        Schema::table('maids_d_b_s', function (Blueprint $table) {
            // Rollback: drop new columns
            $table->dropColumn(['video_id', 'video_link_s']);
            
            // Restore old video_id as string
            $table->string('video_id')->nullable()->after('name');
        });
    }
};
