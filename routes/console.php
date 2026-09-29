<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Penjadwalan Otomatis Notifikasi Telegram & In-App SIMAPO Apotek Bintan 88
 * Jalankan cron di server: * * * * * cd /path-to-simapo && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('notifikasi:cek-stok')->dailyAt('08:00');
Schedule::command('notifikasi:cek-expired')->dailyAt('08:30');
Schedule::command('notifikasi:cek-hutang')->dailyAt('09:00');
Schedule::command('notifikasi:ringkasan-harian')->dailyAt('21:00');
