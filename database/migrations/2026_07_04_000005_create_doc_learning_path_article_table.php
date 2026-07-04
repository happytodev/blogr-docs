<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_learning_path_article', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_learning_path_id')->constrained('doc_learning_paths')->cascadeOnDelete();
            $table->foreignId('doc_article_id')->constrained('doc_articles')->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['doc_learning_path_id', 'doc_article_id']);
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_learning_path_article');
    }
};
