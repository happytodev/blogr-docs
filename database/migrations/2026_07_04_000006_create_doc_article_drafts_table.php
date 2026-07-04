<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_article_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_article_translation_id')->nullable()->unique()->constrained('doc_article_translations')->cascadeOnDelete();
            $table->foreignId('doc_article_id')->nullable()->unique()->constrained('doc_articles')->cascadeOnDelete();
            $table->json('draft_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_article_drafts');
    }
};
