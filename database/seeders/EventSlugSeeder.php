<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;

class EventSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Generating/updating slugs for existing events...');

        Event::all()->each(function ($event) {
            $oldSlug = $event->slug;
            $baseSlug = Str::slug($event->title);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure unique slug
            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $event->update(['slug' => $slug]);

            if ($oldSlug !== $slug) {
                $this->command->info("Updated slug from '{$oldSlug}' to '{$slug}' for event: {$event->title}");
            } else {
                $this->command->info("Slug '{$slug}' already correct for event: {$event->title}");
            }
        });

        $this->command->info('Slug generation/update completed!');

        // Show summary
        $eventsWithSlug = Event::whereNotNull('slug')->where('slug', '!=', '')->count();
        $totalEvents = Event::count();
        $this->command->info("Total events: {$totalEvents}");
        $this->command->info("Events with slug: {$eventsWithSlug}");

        // Show sample slugs
        $this->command->info("\nSample slugs generated:");
        Event::take(5)->get()->each(function ($event) {
            $this->command->info("- {$event->title} → {$event->slug}");
        });
    }
}
