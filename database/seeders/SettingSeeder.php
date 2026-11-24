<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::write([
            'cafe_name' => 'Kopiah',
            'tagline' => 'Ngopi santai, momen jadi berarti',
            'opening_hours' => 'Setiap hari, 08.00 - 22.00 WIB',
            'address' => 'Jl. Contoh No. 10, Depok, Jawa Barat',
            'whatsapp' => '6281234567890',
            'instagram' => '@kopiah.id',
            'logo_path' => 'assets/img/logo-kopiah.png',
            'tax_percent' => '0',
            'service_percent' => '0',
            'qris_info' => 'Silakan scan QRIS yang tersedia di kasir. Tunjukkan bukti pembayaran kepada kasir.',
            'google_maps_embed' => 'https://www.google.com/maps?q=Depok%20Jawa%20Barat&output=embed',
        ]);
    }
}
