<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        Promo::firstOrCreate(['kode' => 'NGOPI10'], [
            'tipe' => 'persen',
            'nilai' => 10,
            'min_belanja' => 30000,
            'kuota' => 100,
            'mulai' => now(),
            'selesai' => now()->addYear(),
            'is_active' => true,
        ]);
    }
}
