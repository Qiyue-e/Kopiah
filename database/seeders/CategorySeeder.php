<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Kopi', 'Non-Kopi', 'Makanan', 'Snack', 'Dessert'] as $i => $nama) {
            Category::firstOrCreate(['nama' => $nama], ['urutan' => $i + 1]);
        }
    }
}
