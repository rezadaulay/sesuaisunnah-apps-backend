<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DonationSettings;

class DonationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DonationSettings::create([
            'bank_name' => 'Bank Syariah Indonesia',
            'account_number' => '1234567890',
            'account_name' => 'Yayasan Sesuai Sunnah',
            'swift_code' => 'BSINIDJA',
            'branch_name' => 'Cabang Jakarta Pusat',
            'donation_note' => 'Terima kasih atas donasi Anda. Semoga Allah SWT membalas kebaikan Anda dengan berlipat ganda. Donasi Anda akan digunakan untuk pengembangan dakwah dan kegiatan keislaman.',
            'bank_transfer_note' => 'Mohon cantumkan nama donatur pada kolom berita transfer untuk memudahkan kami dalam pencatatan dan laporan.',
            'minimum_donation' => 10000.00,
            'is_active' => true,
            'show_donor_list' => false,
            'contact_person' => 'Ustadz Ahmad - 08123456789',
            'contact_email' => 'donasi@sesuaisunnah.org',
        ]);

        $this->command->info('Sample donation settings have been seeded successfully!');
    }
}
