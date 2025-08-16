<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use App\Models\EventRegistration;

class EventRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();
        $users = User::where('email', '!=', 'daulayreza@gmail.com')->get();

        if ($events->isEmpty()) {
            $this->command->error('No events found. Please run EventSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run AdminUserSeeder first.');
            return;
        }

        $referralSources = ['Instagram', 'WhatsApp', 'Website', 'Teman', 'Facebook'];

        foreach ($events as $event) {
            // Skip past events
            if ($event->event_date < now()) {
                continue;
            }

            // Create 2-5 registrations per event
            $registrationCount = rand(2, 5);

            for ($i = 0; $i < $registrationCount; $i++) {
                $user = $users->random();
                $referralSource = $referralSources[array_rand($referralSources)];

                // Check if user already registered for this event
                $existingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$existingRegistration) {
                    EventRegistration::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'referral_source' => $referralSource,
                        'registered_at' => now()->subDays(rand(1, 7)),
                    ]);
                }
            }
        }

        $this->command->info('Sample event registrations created successfully!');
    }
}
