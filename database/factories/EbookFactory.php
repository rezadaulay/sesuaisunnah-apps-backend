<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ebook>
 */
class EbookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isFree = $this->faker->boolean(30); // 30% chance of being free
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'cover_image' => null,
            'price' => $isFree ? 0 : $this->faker->randomFloat(2, 50000, 500000), // 50k to 500k IDR
            'file_url' => 'ebooks/files/' . $this->faker->uuid . '.pdf',
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the ebook is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
        ]);
    }

    /**
     * Indicate that the ebook is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }

    /**
     * Indicate that the ebook has a cover image.
     */
    public function withCover(): static
    {
        return $this->state(fn (array $attributes) => [
            'cover_image' => 'ebooks/covers/' . $this->faker->uuid . '.jpg',
        ]);
    }

    /**
     * Indicate that the ebook is a PDF.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => 'ebooks/files/' . $this->faker->uuid . '.pdf',
        ]);
    }

    /**
     * Indicate that the ebook is an EPUB.
     */
    public function epub(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => 'ebooks/files/' . $this->faker->uuid . '.epub',
        ]);
    }

    /**
     * Indicate that the ebook is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 200000, 1000000), // 200k to 1M IDR
        ]);
    }

    /**
     * Indicate that the ebook is cheap.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 10000, 100000), // 10k to 100k IDR
        ]);
    }
}
