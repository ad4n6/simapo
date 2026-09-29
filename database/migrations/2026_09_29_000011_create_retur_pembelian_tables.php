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
        Schema::create('retur_pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur')->unique();
            $table->foreignId('pembelian_id')->nullable()->constrained('pembelian')->nullOnDelete();
            $table->foreignId('suplier_id')->constrained('suplier')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_retur');
            $table->decimal('total_nilai', 15, 2)->default(0);
            $table->enum('status', ['diajukan', 'disetujui', 'selesai'])->default('diajukan');
            $table->text('alasan');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('retur_pembelian_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_pembelian_id')->constrained('retur_pembelian')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_detail');
        Schema::dropIfExists('retur_pembelian');
    }
};
