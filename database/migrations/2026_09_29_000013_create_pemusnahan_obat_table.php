<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemusnahan_obat', function (Blueprint $table) {
            $table->id();
            $table->string('no_pemusnahan')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->integer('jumlah');
            $table->text('alasan');
            $table->string('saksi')->nullable();
            $table->string('metode_pemusnahan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemusnahan_obat');
    }
};
