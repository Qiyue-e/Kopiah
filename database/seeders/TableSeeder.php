<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tokens = [
            '01' => '3a65d19b92c7', '02' => '5d91f8a0b62e', '03' => '7bb2a406d3f1', '04' => '9c124a7f55d0',
            '05' => '1f8e39b6a402', '06' => '4cb6d087a9e3', '07' => '8aa16f37c5d2', '08' => '2e9d61c4f807',
            '09' => '6fb3a8421d95', '10' => 'a13c74e90b26',
        ];

        foreach ($tokens as $nomor => $suffix) {
            DiningTable::firstOrCreate(['nomor_meja' => $nomor], ['qr_token' => "kopiah-meja-{$nomor}-qr-token-{$suffix}"]);
        }
    }
}
