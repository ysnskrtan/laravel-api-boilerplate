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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('featured_image')->nullable();
            $table->json('meta_data')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index('slug');
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}; 