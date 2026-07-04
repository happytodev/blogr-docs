<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('doc_articles')->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->string('icon')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('default_locale', 2)->default('en');
            $table->boolean('display_toc')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'position']);
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_articles');
    }
};
