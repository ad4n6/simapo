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
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('nama_generik')->nullable();
            $table->foreignId('kategori_id')->constrained('kategori')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('stok_minimum')->default(10);
            $table->string('lokasi_rak')->nullable();
            $table->boolean('butuh_resep')->default(false);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('suplier_id')->nullable()->constrained('suplier')->nullOnDelete();
            $table->string('no_batch');
            $table->date('tanggal_kedaluwarsa');
            $table->integer('jumlah_masuk')->default(0);
            $table->integer('jumlah_saat_ini')->default(0);
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['obat_id', 'tanggal_kedaluwarsa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_obat');
        Schema::dropIfExists('obat');
    }
};
