<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('modal_awal', 12, 2)->default(0);
            $table->decimal('kas_akhir', 12, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('dibuka_pada')->useCurrent();
            $table->dateTime('ditutup_pada')->nullable();

            $table->index(['user_id', 'ditutup_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
