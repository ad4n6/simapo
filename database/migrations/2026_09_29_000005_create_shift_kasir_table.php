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
        Schema::create('shift_kasir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('waktu_buka');
            $table->dateTime('waktu_tutup')->nullable();
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->decimal('total_penjualan_tunai', 15, 2)->default(0);
            $table->decimal('total_penjualan_non_tunai', 15, 2)->default(0);
            $table->decimal('uang_fisik_akhir', 15, 2)->nullable();
            $table->decimal('selisih', 15, 2)->nullable();
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_kasir');
    }
};
