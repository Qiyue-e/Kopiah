<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Publikasikan foto lama yang menunggu; foto rejected tetap rejected. */
    public function up(): void
    {
        if (Schema::hasTable('moments')) {
            DB::table('moments')->where('status', 'pending')->update(['status' => 'approved']);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'moment_auto_approve')->delete();
        }
    }

    /** Sengaja tidak mengembalikan foto ke pending agar rollback tidak menyembunyikan momen pelanggan. */
    public function down(): void
    {
        // Tidak ada perubahan balik otomatis untuk data publikasi pelanggan.
    }
};
