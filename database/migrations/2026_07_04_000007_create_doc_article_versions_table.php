<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_article_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_article_translation_id')->constrained('doc_article_translations')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->timestamps();

            $table->unique(['doc_article_translation_id', 'version_number']);
            $table->index('version_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_article_versions');
    }
};
