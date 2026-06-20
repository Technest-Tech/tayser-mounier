<?php

use App\Enums\BookStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            // Full book file (private). Only ever served for free books.
            $table->string('file')->nullable();
            // Free sample/preview file (private). Served for paid books.
            $table->string('sample')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->string('status')->default(BookStatus::Draft->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
