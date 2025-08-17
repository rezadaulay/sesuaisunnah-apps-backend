<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ebook;
use App\Models\User;

class EbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by
        $adminUser = User::first() ?? User::factory()->create();

        $ebooks = [
            [
                'title' => 'Panduan Lengkap Shalat Fardhu',
                'description' => 'Buku panduan lengkap untuk mempelajari tata cara shalat fardhu yang benar sesuai sunnah Rasulullah SAW. Dilengkapi dengan ilustrasi dan penjelasan detail setiap gerakan.',
                'price' => 0.00, // Free
                'file_url' => 'ebooks/shalat-fardhu.pdf',
                'cover_image' => 'ebooks/covers/shalat-fardhu.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Kumpulan Doa Harian Muslim',
                'description' => 'Kumpulan doa-doa harian yang diajarkan Rasulullah SAW, mulai dari bangun tidur hingga tidur kembali. Setiap doa dilengkapi dengan arti dan keutamaan.',
                'price' => 25000.00,
                'file_url' => 'ebooks/doa-harian.pdf',
                'cover_image' => 'ebooks/covers/doa-harian.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Tafsir Al-Quran Juz Amma',
                'description' => 'Tafsir ringkas untuk Juz Amma (Juz 30) yang berisi surat-surat pendek. Penjelasan yang mudah dipahami untuk pemula dalam mempelajari Al-Quran.',
                'price' => 45000.00,
                'file_url' => 'ebooks/tafsir-juz-amma.pdf',
                'cover_image' => 'ebooks/covers/tafsir-juz-amma.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Sejarah Nabi Muhammad SAW',
                'description' => 'Biografi lengkap Rasulullah SAW dari masa kecil hingga wafat. Menggambarkan perjalanan dakwah dan teladan kehidupan yang patut ditiru.',
                'price' => 35000.00,
                'file_url' => 'ebooks/sejarah-nabi.pdf',
                'cover_image' => 'ebooks/covers/sejarah-nabi.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Fiqh Puasa dan Ramadhan',
                'description' => 'Panduan lengkap tentang hukum puasa, amalan Ramadhan, dan hal-hal yang berkaitan dengan ibadah di bulan suci. Dilengkapi dengan dalil dan penjelasan.',
                'price' => 30000.00,
                'file_url' => 'ebooks/fiqh-puasa.pdf',
                'cover_image' => 'ebooks/covers/fiqh-puasa.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Adab dan Akhlak Muslim',
                'description' => 'Buku tentang adab dan akhlak yang baik dalam Islam. Berisi tuntunan berperilaku sesuai dengan ajaran Rasulullah SAW dalam kehidupan sehari-hari.',
                'price' => 0.00, // Free
                'file_url' => 'ebooks/adab-akhlak.pdf',
                'cover_image' => 'ebooks/covers/adab-akhlak.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Panduan Haji dan Umrah',
                'description' => 'Panduan praktis untuk melaksanakan ibadah haji dan umrah. Berisi tata cara, doa, dan hal-hal yang perlu diperhatikan selama menjalankan ibadah.',
                'price' => 55000.00,
                'file_url' => 'ebooks/panduan-haji.pdf',
                'cover_image' => 'ebooks/covers/panduan-haji.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Kisah 25 Nabi dan Rasul',
                'description' => 'Kumpulan kisah inspiratif 25 Nabi dan Rasul yang disebutkan dalam Al-Quran. Setiap kisah mengandung hikmah dan pelajaran berharga.',
                'price' => 40000.00,
                'file_url' => 'ebooks/kisah-nabi.pdf',
                'cover_image' => 'ebooks/covers/kisah-nabi.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Tauhid dan Aqidah Islam',
                'description' => 'Buku tentang dasar-dasar keimanan dan keyakinan dalam Islam. Penjelasan tentang rukun iman dan hal-hal yang berkaitan dengan aqidah.',
                'price' => 0.00, // Free
                'file_url' => 'ebooks/tauhid-aqidah.pdf',
                'cover_image' => 'ebooks/covers/tauhid-aqidah.jpg',
                'created_by' => $adminUser->id,
            ],
            [
                'title' => 'Panduan Zakat dan Sedekah',
                'description' => 'Panduan lengkap tentang zakat, sedekah, dan amal sosial dalam Islam. Berisi hukum, cara perhitungan, dan keutamaan berbagi.',
                'price' => 28000.00,
                'file_url' => 'ebooks/panduan-zakat.pdf',
                'cover_image' => 'ebooks/covers/panduan-zakat.jpg',
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($ebooks as $ebookData) {
            Ebook::create($ebookData);
        }

        $this->command->info('Sample e-books have been seeded successfully!');
    }
}
