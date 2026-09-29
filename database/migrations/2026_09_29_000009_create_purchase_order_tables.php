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
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->foreignId('suplier_id')->constrained('suplier')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_po');
            $table->date('estimasi_tanggal_terima')->nullable();
            $table->decimal('total_perkiraan', 15, 2)->default(0);
            $table->enum('status', ['draft', 'dipesan', 'diterima_sebagian', 'diterima', 'batal'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tanggal_po']);
        });

        Schema::create('purchase_order_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_order')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('jumlah_dipesan');
            $table->integer('jumlah_diterima')->default(0);
            $table->decimal('harga_satuan_perkiraan', 15, 2)->default(0);
            $table->decimal('subtotal_perkiraan', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_detail');
        Schema::dropIfExists('purchase_order');
    }
};
