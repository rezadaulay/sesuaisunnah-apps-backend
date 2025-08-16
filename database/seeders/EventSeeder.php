<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'daulayreza@gmail.com')->first();
        
        if (!$admin) {
            $this->command->error('Admin user not found. Please run AdminUserSeeder first.');
            return;
        }

        $events = [
            [
                'title' => 'Kajian Rutin Ahad Pagi',
                'description' => 'Kajian rutin setiap hari Ahad pagi membahas berbagai topik keislaman yang relevan dengan kehidupan sehari-hari.',
                'event_date' => now()->addDays(7),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Workshop Parenting Islami',
                'description' => 'Workshop khusus untuk orang tua dalam mendidik anak sesuai dengan nilai-nilai Islam dan sunnah Rasulullah ﷺ.',
                'event_date' => now()->addDays(14),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Seminar Ekonomi Syariah',
                'description' => 'Seminar membahas prinsip-prinsip ekonomi syariah dan implementasinya dalam kehidupan modern.',
                'event_date' => now()->addDays(21),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Kajian Tafsir Al-Quran',
                'description' => 'Kajian mendalam tentang tafsir Al-Quran dengan pendekatan yang mudah dipahami.',
                'event_date' => now()->addDays(28),
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Event Ramadhan 1446 H',
                'description' => 'Serangkaian kegiatan selama bulan Ramadhan termasuk tarawih berjamaah, sahur bersama, dan buka puasa bersama.',
                'event_date' => now()->addMonths(2),
                'created_by' => $admin->id,
            ],
        ];

        foreach ($events as $eventData) {
            Event::firstOrCreate(
                ['title' => $eventData['title']],
                $eventData
            );
        }

        $this->command->info('Sample events created successfully!');
    }
}
