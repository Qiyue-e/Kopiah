<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();
            $table->enum('tipe', ['persen', 'nominal'])->default('persen');
            $table->decimal('nilai', 12, 2)->default(0);
            $table->decimal('min_belanja', 12, 2)->default(0);
            $table->unsignedInteger('kuota')->default(0)->comment('0 = tanpa batas');
            $table->dateTime('mulai')->nullable();
            $table->dateTime('selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'mulai', 'selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
