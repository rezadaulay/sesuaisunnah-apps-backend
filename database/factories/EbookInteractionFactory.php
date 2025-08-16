<?php

namespace Database\Factories;

use App\Models\Ebook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EbookInteraction>
 */
class EbookInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['read', 'download', 'listen'];
        
        return [
            'ebook_id' => Ebook::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement($actions),
        ];
    }

    /**
     * Indicate that the interaction is a read action.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'read',
        ]);
    }

    /**
     * Indicate that the interaction is a download action.
     */
    public function download(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'download',
        ]);
    }

    /**
     * Indicate that the interaction is a listen action.
     */
    public function listen(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'listen',
        ]);
    }

    /**
     * Indicate that the interaction is for a specific ebook.
     */
    public function forEbook(Ebook $ebook): static
    {
        return $this->state(fn (array $attributes) => [
            'ebook_id' => $ebook->id,
        ]);
    }

    /**
     * Indicate that the interaction is by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the interaction is recent (within last 7 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the interaction is old (more than 30 days ago).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }
}
