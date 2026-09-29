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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_order')->nullOnDelete();
            $table->foreignId('suplier_id')->constrained('suplier')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_faktur');
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->enum('jenis_pembayaran', ['tunai', 'kredit'])->default('tunai');
            $table->enum('status_pembayaran', ['lunas', 'belum_lunas'])->default('lunas');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->decimal('total_terbayar', 15, 2)->default(0);
            $table->decimal('sisa_hutang', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal_faktur', 'status_pembayaran']);
            $table->index(['suplier_id', 'status_pembayaran']);
        });

        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->nullable()->constrained('batch_obat')->nullOnDelete();
            $table->string('no_batch');
            $table->date('tanggal_kedaluwarsa');
            $table->integer('jumlah');
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['pembelian_id', 'obat_id']);
        });

        Schema::create('pembayaran_hutang', function (Blueprint $table) {
            $table->id();
            $table->string('no_pembayaran')->unique();
            $table->foreignId('pembelian_id')->constrained('pembelian')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->string('metode_bayar')->default('transfer');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['pembelian_id', 'tanggal_bayar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_hutang');
        Schema::dropIfExists('pembelian_detail');
        Schema::dropIfExists('pembelian');
    }
};
