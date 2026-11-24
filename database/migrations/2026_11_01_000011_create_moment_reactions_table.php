<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moment_id')->constrained('moments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('device_token', 64);
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['moment_id', 'device_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moment_reactions');
    }
};
