<?php

namespace Tests\Unit;

use App\Support\MenuImagePath;
use Tests\TestCase;

class MenuImagePathTest extends TestCase
{
    public function test_normalizes_current_and_legacy_public_storage_paths(): void
    {
        $this->assertSame('menus/chocolate-cloud.webp', MenuImagePath::storagePath('menus/chocolate-cloud.webp'));
        $this->assertSame('menus/chocolate-cloud.webp', MenuImagePath::storagePath('/storage/menus/chocolate-cloud.webp'));
        $this->assertSame('menus/chocolate-cloud.webp', MenuImagePath::storagePath('storage/app/public/menus/chocolate-cloud.webp'));
        $this->assertSame('menus/chocolate-cloud.webp', MenuImagePath::storagePath('public/storage/menus/chocolate-cloud.webp'));
    }

    public function test_rejects_path_traversal_and_non_menu_assets_as_storage_paths(): void
    {
        $this->assertNull(MenuImagePath::storagePath('menus/../../.env'));
        $this->assertNull(MenuImagePath::storagePath('assets/img/logo-kopiah.png'));
        $this->assertNull(MenuImagePath::storagePath('storage/framework/sessions/session'));
    }

    public function test_uses_relative_local_url_for_uploaded_menu_images(): void
    {
        $url = MenuImagePath::url('menus/chocolate-cloud.webp');

        $this->assertStringStartsWith('/menu-image/', $url);
        $this->assertStringContainsString('chocolate-cloud.webp', $url);
        $this->assertStringNotContainsString('localhost', $url);
    }

    public function test_keeps_external_image_cdn_urls_unchanged(): void
    {
        $url = 'https://images.unsplash.com/photo-example?auto=format&fit=crop';

        $this->assertSame($url, MenuImagePath::url($url));
    }
}
