<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::pluck('id', 'nama');
        $ukuran = [['Ukuran', 'Regular', 0], ['Ukuran', 'Large', 5000]];
        $suhu = [['Suhu', 'Hot', 0], ['Suhu', 'Ice', 0]];
        $u = fn (string $id) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=900&q=85";

        $menus = [
            ['Kopi', 'Kopiah Aren Latte', 'Espresso, susu creamy, dan manis gula aren yang pas.', 24000, $u('1461023058943-07fcbe16d735'), 35, true,
                [...$ukuran, ...$suhu, ['Gula', 'Less Sugar', 0], ['Topping', 'Extra Espresso', 6000]]],
            ['Kopi', 'Americano', 'Espresso dengan air pilihan, ringan dan menyegarkan.', 18000, $u('1517701604599-bb29b565090c'), 30, false,
                [...$ukuran, ...$suhu]],
            ['Kopi', 'Caramel Macchiato', 'Kopi susu dengan sentuhan karamel.', 27000, $u('1485808191679-5f86510681a2'), 25, true,
                [...$ukuran, ...$suhu, ['Topping', 'Extra Caramel', 4000]]],
            ['Kopi', 'Cappuccino', 'Perpaduan espresso, susu, dan foam lembut.', 23000, $u('1572442388796-11668a67e53d'), 28, false,
                [...$ukuran, ...$suhu]],
            ['Non-Kopi', 'Matcha Latte', 'Matcha dengan susu yang lembut dan earthy.', 26000, $u('1515823064-d6e0c04616a7'), 20, true,
                [...$ukuran, ['Suhu', 'Ice', 0], ['Topping', 'Oat Milk', 6000]]],
            ['Non-Kopi', 'Chocolate Cloud', 'Cokelat pekat dengan tekstur creamy.', 24000, $u('1542990253-0b8be4edc6c6'), 20, false,
                [...$ukuran, ...$suhu]],
            ['Makanan', 'Chicken Mentai Rice', 'Nasi hangat dengan ayam dan saus mentai gurih.', 32000, $u('1512058564366-18510be2db19'), 15, true,
                [['Topping', 'Extra Sambal', 3000], ['Topping', 'Telur', 5000]]],
            ['Snack', 'French Fries', 'Kentang goreng renyah dengan bumbu pilihan.', 18000, $u('1573080496219-bb080dd4f877'), 22, false,
                [['Topping', 'Cheese Powder', 3000], ['Topping', 'BBQ Powder', 3000]]],
            ['Snack', 'Butter Croissant', 'Croissant berlapis dengan aroma butter.', 20000, $u('1555507036-ab1f4038808a'), 12, false, []],
            ['Dessert', 'Tiramisu Cup', 'Dessert lembut dengan kopi dan cokelat.', 25000, $u('1571877227200-a0d98ea607e9'), 10, true, []],
        ];

        foreach ($menus as [$kategori, $nama, $deskripsi, $harga, $foto, $stok, $best, $opsi]) {
            $menu = Menu::firstOrCreate(['nama' => $nama], [
                'category_id' => $cat[$kategori],
                'deskripsi' => $deskripsi,
                'harga' => $harga,
                'foto' => $foto,
                'stok' => $stok,
                'is_available' => true,
                'is_best_seller' => $best,
            ]);

            if ($menu->wasRecentlyCreated) {
                $menu->options()->createMany(array_map(
                    fn ($o) => ['tipe' => $o[0], 'nama_opsi' => $o[1], 'harga_tambahan' => $o[2]],
                    $opsi
                ));
            }
        }
    }
}
