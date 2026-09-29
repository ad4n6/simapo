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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('role_target')->nullable();
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe');
            $table->string('link')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_at']);
            $table->index(['role_target', 'dibaca_at']);
        });

        Schema::create('notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');
            $table->text('pesan');
            $table->string('tujuan');
            $table->enum('status', ['terkirim', 'gagal']);
            $table->text('response_error')->nullable();
            $table->string('entitas_tipe')->nullable();
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'entitas_tipe', 'entitas_id', 'created_at'], 'notif_log_anti_spam_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_log');
        Schema::dropIfExists('notifikasi');
    }
};
