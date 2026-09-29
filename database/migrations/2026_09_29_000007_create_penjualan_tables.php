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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_kasir_id')->nullable()->constrained('shift_kasir')->nullOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->nullOnDelete();
            $table->foreignId('resep_id')->nullable()->constrained('resep')->nullOnDelete();
            $table->enum('jenis_transaksi', ['biasa', 'resep'])->default('biasa');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->enum('status', ['selesai', 'void'])->default('selesai');
            $table->text('void_alasan')->nullable();
            $table->foreignId('void_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('void_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->nullable()->constrained('batch_obat')->nullOnDelete();
            $table->string('no_batch')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('jumlah');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['penjualan_id', 'obat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
        Schema::dropIfExists('penjualan');
    }
};
