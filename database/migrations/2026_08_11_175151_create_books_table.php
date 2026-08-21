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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique();
            $table->unsignedBigInteger('author_id')->constrained('authors')->restrictOnDelete();    
            $table->unsignedBigInteger('publisher_id')->constrained('publishers')->restrictOnDelete();
            $table->unsignedBigInteger('category_id')->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('edition')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('language');
            $table->unsignedInteger('pages')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('copies')->default(0);
            $table->unsignedInteger('available_copies')->default(0);
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
