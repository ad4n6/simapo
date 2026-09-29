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
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->string('no_opname')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['draft', 'selesai'])->default('selesai');
            $table->integer('total_selisih_plus')->default(0);
            $table->integer('total_selisih_minus')->default(0);
            $table->decimal('total_nilai_selisih', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opname')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('stok_sistem');
            $table->integer('stok_fisik');
            $table->integer('selisih');
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('nilai_selisih', 15, 2)->default(0);
            $table->string('alasan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_detail');
        Schema::dropIfExists('stock_opname');
    }
};
