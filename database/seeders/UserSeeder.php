<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Akun demo — owner segera ganti password; password kasir direset owner lewat menu Akun Kasir. */
    public function run(): void
    {
        User::firstOrCreate(['username' => 'owner'], [
            'nama' => 'Owner Kopiah',
            'password' => 'Kopiah#2026!', // otomatis di-hash oleh cast `hashed`
            'role' => 'owner',
            'is_active' => true,
        ]);

        User::firstOrCreate(['username' => 'kasir'], [
            'nama' => 'Kasir Demo',
            'password' => 'Kasir#2026!',
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }
}
