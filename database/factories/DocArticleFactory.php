<?php

namespace Happytodev\BlogrDocs\Database\Factories;

use Happytodev\BlogrDocs\Models\DocArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocArticleFactory extends Factory
{
    protected $model = DocArticle::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'position' => 0,
            'icon' => null,
            'is_published' => true,
            'published_at' => now(),
            'default_locale' => 'en',
            'display_toc' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    public function childOf(DocArticle $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
