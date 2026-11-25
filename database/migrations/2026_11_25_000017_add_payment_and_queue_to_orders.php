<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Token acak untuk link pesanan (halaman bayar, sukses, struk) agar tidak bisa ditebak dari kode pesanan.
            $table->string('token', 40)->nullable()->unique()->after('kode_pesanan');
            $table->unsignedInteger('nomor_antrean')->nullable()->after('token');
            $table->date('tanggal_antrean')->nullable()->after('nomor_antrean');
            $table->dateTime('expires_at')->nullable()->after('status_pesanan');
            $table->dateTime('paid_at')->nullable()->after('expires_at');
            $table->string('xendit_qr_id', 80)->nullable()->after('paid_at');
            $table->text('qr_string')->nullable()->after('xendit_qr_id');
            $table->string('xendit_payment_id', 80)->nullable()->after('qr_string');

            // Satu nomor antrean per hari; menjamin tidak ada duplikat walau ada bug di aplikasi.
            $table->unique(['tanggal_antrean', 'nomor_antrean'], 'orders_antrean_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status_bayar', ['Belum Bayar', 'Menunggu QRIS', 'Lunas', 'Gagal', 'Kedaluwarsa'])
                ->default('Belum Bayar')->change();
        });

        // Counter antrean harian. Satu baris per tanggal (zona waktu Asia/Jakarta).
        Schema::create('queue_counters', function (Blueprint $table) {
            $table->date('tanggal')->primary();
            $table->unsignedInteger('terakhir')->default(0);
        });

        // Pesanan lama ikut mendapat token supaya struknya bisa dibuka lewat link.
        DB::table('orders')->whereNull('token')->orderBy('id')->each(function ($row) {
            DB::table('orders')->where('id', $row->id)->update(['token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_counters');

        DB::table('orders')->where('status_bayar', 'Kedaluwarsa')->update(['status_bayar' => 'Gagal']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status_bayar', ['Belum Bayar', 'Menunggu QRIS', 'Lunas', 'Gagal'])
                ->default('Belum Bayar')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_antrean_unique');
            $table->dropUnique(['token']);
            $table->dropColumn([
                'token', 'nomor_antrean', 'tanggal_antrean', 'expires_at', 'paid_at',
                'xendit_qr_id', 'qr_string', 'xendit_payment_id',
            ]);
        });
    }
};
