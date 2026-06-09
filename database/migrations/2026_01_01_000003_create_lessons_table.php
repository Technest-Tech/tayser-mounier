<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('section')->nullable();
            $table->string('title');
            $table->string('source'); // bunny | youtube
            $table->string('video_id');
            $table->unsignedInteger('duration')->nullable(); // seconds
            $table->boolean('is_preview')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
