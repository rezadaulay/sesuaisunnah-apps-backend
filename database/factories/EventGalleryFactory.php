<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGallery>
 */
class EventGalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['photo', 'video', 'document'];
        $type = $this->faker->randomElement($types);
        
        $mimeTypes = [
            'photo' => ['image/jpeg', 'image/png', 'image/gif'],
            'video' => ['video/mp4', 'video/mov', 'video/avi'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];
        
        $fileExtensions = [
            'photo' => ['jpg', 'jpeg', 'png', 'gif'],
            'video' => ['mp4', 'mov', 'avi'],
            'document' => ['pdf', 'doc', 'docx']
        ];
        
        $extension = $this->faker->randomElement($fileExtensions[$type]);
        $mimeType = $this->faker->randomElement($mimeTypes[$type]);
        
        return [
            'event_id' => Event::factory(),
            'photo_url' => 'events/' . $this->faker->numberBetween(1, 10) . '/documentation/' . $this->faker->uuid . '.' . $extension,
            'type' => $type,
            'description' => $this->faker->sentence(6),
            'file_size' => $this->faker->numberBetween(100000, 5000000), // 100KB to 5MB
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Indicate that the gallery item is a photo.
     */
    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'photo',
            'photo_url' => 'events/' . $this->faker->numberBetween(1, 10) . '/documentation/' . $this->faker->uuid . '.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * Indicate that the gallery item is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'photo_url' => 'events/' . $this->faker->numberBetween(1, 10) . '/documentation/' . $this->faker->uuid . '.mp4',
            'mime_type' => 'video/mp4',
        ]);
    }

    /**
     * Indicate that the gallery item is a document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'photo_url' => 'events/' . $this->faker->numberBetween(1, 10) . '/documentation/' . $this->faker->uuid . '.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }
}
