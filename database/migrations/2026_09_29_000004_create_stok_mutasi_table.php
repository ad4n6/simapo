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
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('batch_obat_id')->nullable()->constrained('batch_obat')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar', 'penyesuaian', 'retur_penjualan', 'retur_pembelian', 'pemusnahan']);
            $table->integer('jumlah');
            $table->integer('saldo_sebelum');
            $table->integer('saldo_sesudah');
            $table->string('referensi_tipe')->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['obat_id', 'created_at']);
            $table->index(['referensi_tipe', 'referensi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
