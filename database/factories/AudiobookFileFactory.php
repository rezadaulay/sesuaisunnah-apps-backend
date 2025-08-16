<?php

namespace Database\Factories;

use App\Models\Ebook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AudiobookFile>
 */
class AudiobookFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['mp3', 'm4a', 'aac'];
        $extension = $this->faker->randomElement($extensions);
        
        return [
            'ebook_id' => Ebook::factory(),
            'name' => $this->faker->sentence(3),
            'file_url' => 'ebooks/audiobooks/' . $this->faker->numberBetween(1, 10) . '/' . $this->faker->uuid . '.' . $extension,
            'order_number' => $this->faker->numberBetween(1, 20),
        ];
    }

    /**
     * Indicate that the audiobook file is an MP3.
     */
    public function mp3(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => 'ebooks/audiobooks/' . $this->faker->numberBetween(1, 10) . '/' . $this->faker->uuid . '.mp3',
        ]);
    }

    /**
     * Indicate that the audiobook file is an M4A.
     */
    public function m4a(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => 'ebooks/audiobooks/' . $this->faker->numberBetween(1, 10) . '/' . $this->faker->uuid . '.m4a',
        ]);
    }

    /**
     * Indicate that the audiobook file is an AAC.
     */
    public function aac(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_url' => 'ebooks/audiobooks/' . $this->faker->numberBetween(1, 10) . '/' . $this->faker->uuid . '.aac',
        ]);
    }

    /**
     * Indicate that the audiobook file has a specific order number.
     */
    public function orderNumber(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'order_number' => $number,
        ]);
    }

    /**
     * Indicate that the audiobook file has a chapter name.
     */
    public function chapter(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
