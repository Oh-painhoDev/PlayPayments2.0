<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('disputes:process-expired')->hourly();

// Verificar transações PIX pendentes a cada minuto
// Para verificar a cada segundo, execute manualmente: php artisan pix:check-pending --daemon
Schedule::command('pix:check-pending --limit=10')->everyMinute();
