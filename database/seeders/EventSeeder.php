<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

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
            // Event yang sudah lewat (past)
            [
                'title' => 'Kajian Rutin Ahad Pagi - Edisi Januari',
                'slug' => 'kajian-rutin-ahad-pagi-edisi-januari',
                'description' => 'Kajian rutin setiap hari Ahad pagi membahas berbagai topik keislaman yang relevan dengan kehidupan sehari-hari.',
                'start_date' => now()->subDays(30)->setTime(8, 0, 0),
                'end_date' => now()->subDays(30)->setTime(10, 0, 0),
                'location' => 'Masjid Al-Ikhlas, Jakarta Selatan',
                'status' => 'event_closed',
                'requires_registration' => false,
                'created_by' => $admin->id,
            ],

            // Event hari ini
            [
                'title' => 'Kajian Tafsir Al-Quran - Surah Al-Baqarah',
                'slug' => 'kajian-tafsir-al-quran-surah-al-baqarah',
                'description' => 'Kajian mendalam tentang tafsir Al-Quran Surah Al-Baqarah dengan pendekatan yang mudah dipahami.',
                'start_date' => now()->setTime(19, 0, 0),
                'end_date' => now()->setTime(21, 0, 0),
                'location' => 'Masjid Nurul Iman, Jakarta Pusat',
                'status' => 'published',
                'requires_registration' => false,
                'created_by' => $admin->id,
            ],

            // Event besok (upcoming)
            [
                'title' => 'Workshop Parenting Islami',
                'slug' => 'workshop-parenting-islami',
                'description' => 'Workshop khusus untuk orang tua dalam mendidik anak sesuai dengan nilai-nilai Islam dan sunnah Rasulullah ﷺ.',
                'start_date' => now()->addDay()->setTime(9, 0, 0),
                'end_date' => now()->addDay()->setTime(16, 0, 0),
                'location' => 'Aula Serbaguna Islamic Center',
                'status' => 'registration_open',
                'requires_registration' => true,
                'registration_opens_at' => now()->subDays(7),
                'registration_closes_at' => now()->addDay()->subHour(),
                'max_participants' => 50,
                'current_participants' => 35,
                'created_by' => $admin->id,
            ],

            // Event minggu depan dengan registrasi dibuka
            [
                'title' => 'Seminar Ekonomi Syariah',
                'slug' => 'seminar-ekonomi-syariah',
                'description' => 'Seminar membahas prinsip-prinsip ekonomi syariah dan implementasinya dalam kehidupan modern.',
                'start_date' => now()->addWeek()->setTime(13, 0, 0),
                'end_date' => now()->addWeek()->setTime(17, 0, 0),
                'location' => 'Hotel Grand Indonesia, Jakarta',
                'status' => 'registration_open',
                'requires_registration' => true,
                'registration_opens_at' => now()->subDays(14),
                'registration_closes_at' => now()->addWeek()->subDay(),
                'max_participants' => 100,
                'current_participants' => 78,
                'created_by' => $admin->id,
            ],

            // Event dengan registrasi belum dibuka
            [
                'title' => 'Kajian Rutin Ahad Pagi - Edisi Februari',
                'slug' => 'kajian-rutin-ahad-pagi-edisi-februari',
                'description' => 'Kajian rutin setiap hari Ahad pagi membahas berbagai topik keislaman yang relevan dengan kehidupan sehari-hari.',
                'start_date' => now()->addDays(10)->setTime(8, 0, 0),
                'end_date' => now()->addDays(10)->setTime(10, 0, 0),
                'location' => 'Masjid Al-Ikhlas, Jakarta Selatan',
                'status' => 'published',
                'requires_registration' => false,
                'created_by' => $admin->id,
            ],

            // Event dengan registrasi ditutup
            [
                'title' => 'Pelatihan Khutbah Jumat',
                'slug' => 'pelatihan-khutbah-jumat',
                'description' => 'Pelatihan intensif untuk para khatib dalam menyusun dan menyampaikan khutbah Jumat yang berkualitas.',
                'start_date' => now()->addDays(15)->setTime(8, 0, 0),
                'end_date' => now()->addDays(15)->setTime(17, 0, 0),
                'location' => 'Islamic Center Jakarta',
                'status' => 'registration_closed',
                'requires_registration' => true,
                'registration_opens_at' => now()->subDays(21),
                'registration_closes_at' => now()->subDays(7),
                'max_participants' => 30,
                'current_participants' => 30,
                'created_by' => $admin->id,
            ],

            // Event dengan kuota penuh
            [
                'title' => 'Workshop Kaligrafi Arab',
                'slug' => 'workshop-kaligrafi-arab',
                'description' => 'Workshop belajar menulis kaligrafi Arab dengan teknik tradisional dan modern.',
                'start_date' => now()->addDays(20)->setTime(10, 0, 0),
                'end_date' => now()->addDays(20)->setTime(15, 0, 0),
                'location' => 'Galeri Seni Islam',
                'status' => 'registration_closed',
                'requires_registration' => true,
                'registration_opens_at' => now()->subDays(14),
                'registration_closes_at' => now()->addDays(5),
                'max_participants' => 25,
                'current_participants' => 25,
                'created_by' => $admin->id,
            ],

            // Event bulan depan
            [
                'title' => 'Event Ramadhan 1446 H',
                'slug' => 'event-ramadhan-1446-h',
                'description' => 'Serangkaian kegiatan selama bulan Ramadhan termasuk tarawih berjamaah, sahur bersama, dan buka puasa bersama.',
                'start_date' => now()->addMonths(2)->setTime(18, 0, 0),
                'end_date' => now()->addMonths(2)->setTime(22, 0, 0),
                'location' => 'Masjid Istiqlal, Jakarta',
                'status' => 'published',
                'requires_registration' => true,
                'registration_opens_at' => now()->addMonth(),
                'registration_closes_at' => now()->addMonths(2)->subWeek(),
                'max_participants' => 200,
                'current_participants' => 0,
                'is_featured' => true, // Event unggulan
                'created_by' => $admin->id,
            ],

            // Event draft
            [
                'title' => 'Seminar Teknologi Islam',
                'slug' => 'seminar-teknologi-islam',
                'description' => 'Seminar membahas bagaimana teknologi dapat digunakan untuk kemajuan umat Islam.',
                'start_date' => now()->addMonths(3)->setTime(9, 0, 0),
                'end_date' => now()->addMonths(3)->setTime(16, 0, 0),
                'location' => 'Convention Center Jakarta',
                'status' => 'draft',
                'requires_registration' => true,
                'registration_opens_at' => now()->addMonths(2),
                'registration_closes_at' => now()->addMonths(3)->subWeek(),
                'max_participants' => 150,
                'current_participants' => 0,
                'created_by' => $admin->id,
            ],

            // Event tanpa batasan kuota
            [
                'title' => 'Kajian Umum Islam',
                'slug' => 'kajian-umum-islam',
                'description' => 'Kajian umum yang terbuka untuk semua kalangan tanpa batasan kuota.',
                'start_date' => now()->addDays(25)->setTime(19, 0, 0),
                'end_date' => now()->addDays(25)->setTime(21, 0, 0),
                'location' => 'Masjid Agung Jakarta',
                'status' => 'registration_open',
                'requires_registration' => true,
                'registration_opens_at' => now()->subDays(10),
                'registration_closes_at' => now()->addDays(25)->subHour(),
                'max_participants' => null, // Tidak terbatas
                'current_participants' => 45,
                'is_featured' => true, // Event unggulan
                'created_by' => $admin->id,
            ],
        ];

        foreach ($events as $eventData) {
            Event::firstOrCreate(
                ['title' => $eventData['title']],
                $eventData
            );
        }

        $this->command->info('Sample events with various conditions created successfully!');
        $this->command->info('Total events created: ' . Event::count());
    }
}
