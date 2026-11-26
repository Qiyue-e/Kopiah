<?php

namespace Tests\Feature;

use App\Models\Moment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeMomentsTest extends TestCase
{
    use RefreshDatabase;

    private function moment(string $path): Moment
    {
        return Moment::create([
            'nama_pelanggan' => 'Tamu', 'caption' => 'x', 'tipe_foto' => 'Makanan',
            'file_path' => $path, 'status' => 'approved', 'device_token' => str_repeat('a', 32), 'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_home_hides_moments_whose_file_is_missing_and_shows_existing_ones(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('moments/ada.jpg', 'x');
        $this->moment('moments/hilang.jpg');
        $ok = $this->moment('moments/ada.jpg');

        $res = $this->get(route('home'))->assertOk();
        $res->assertSee('moments-image/ada.jpg', false)->assertDontSee('moments-image/hilang.jpg', false);
        $this->assertSame([$ok->id], $res->viewData('moments')->pluck('id')->all());
    }

    public function test_home_has_no_moments_section_when_all_files_are_missing(): void
    {
        Storage::fake('public');
        $this->moment('moments/hilang.jpg');

        $this->get(route('home'))->assertOk()->assertDontSee('Moments terbaru');
    }
}
