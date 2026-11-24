<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nama_opsi', 100);
            $table->string('tipe', 40)->default('tambahan')->index();
            $table->decimal('harga_tambahan', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_options');
    }
};
