<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained('menus')->cascadeOnUpdate()->nullOnDelete();
            $table->string('nama_menu', 120);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('harga', 12, 2)->default(0);
            $table->json('opsi_json')->nullable();
            $table->string('catatan', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
