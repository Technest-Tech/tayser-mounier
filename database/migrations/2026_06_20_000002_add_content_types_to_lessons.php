<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // A lesson may now carry any combination of video, audio and PDF,
            // so the video is no longer mandatory.
            $table->string('video_id')->nullable()->change();
            $table->string('audio_path')->nullable()->after('video_id'); // voice file
            $table->string('pdf_path')->nullable()->after('audio_path');  // PDF document
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['audio_path', 'pdf_path']);
            $table->string('video_id')->nullable(false)->change();
        });
    }
};
