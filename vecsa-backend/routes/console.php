<?php

use App\Jobs\CancelUnpaidBoutiqueOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Boutique: Cancel unpaid orders after 72 hours
Schedule::job(new CancelUnpaidBoutiqueOrders)->hourly();
