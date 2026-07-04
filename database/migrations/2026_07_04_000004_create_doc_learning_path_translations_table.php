<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_learning_path_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_learning_path_id')->constrained('doc_learning_paths')->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['doc_learning_path_id', 'locale']);
            $table->unique(['locale', 'slug']);

            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_learning_path_translations');
    }
};
