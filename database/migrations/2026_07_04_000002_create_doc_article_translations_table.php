<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_article_id')->constrained('doc_articles')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->timestamps();

            $table->unique(['doc_article_id', 'locale']);
            $table->unique(['locale', 'slug']);

            $table->index('locale');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_article_translations');
    }
};
