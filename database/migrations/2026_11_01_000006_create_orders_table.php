<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan', 32)->unique();
            $table->string('nama_pelanggan', 100);
            $table->foreignId('table_id')->nullable()->constrained('tables')->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->decimal('pajak', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('metode_bayar', ['Kasir', 'QRIS'])->default('Kasir');
            $table->enum('status_bayar', ['Belum Bayar', 'Menunggu QRIS', 'Lunas', 'Gagal'])->default('Belum Bayar')->index();
            $table->enum('status_pesanan', ['Diterima', 'Diproses', 'Siap', 'Selesai', 'Dibatalkan'])->default('Diterima')->index();
            $table->text('catatan')->nullable();
            $table->foreignId('kasir_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
