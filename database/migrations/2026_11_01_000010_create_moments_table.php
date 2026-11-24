<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moments', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pelanggan', 100)->nullable();
            $table->string('avatar', 16)->nullable()->comment('emoji avatar pengirim');
            $table->string('caption', 500)->nullable();
            $table->enum('tipe_foto', ['Makanan', 'Selfie'])->default('Makanan');
            $table->string('file_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('device_token', 64)->index()->comment('identitas perangkat anonim (tanpa akun)');
            $table->string('ip_address', 45);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moments');
    }
};
