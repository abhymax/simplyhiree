<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SuperadminActivityService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('superadmin:billing-due-check', function (SuperadminActivityService $activityService) {
    $count = $activityService->checkBillingDueAlerts();
    $this->info("Billing due alerts generated: {$count}");
})->purpose('Send superadmin alerts when billing period is due for joined candidates');

Artisan::command('partner:daily-pulse-whatsapp', function (SuperadminActivityService $activityService) {
    $count = $activityService->sendPartnerDailyPulseForAllActivePartners();
    $this->info("Partner daily pulse WhatsApp sent to partners: {$count}");
})->purpose('Send daily pulse WhatsApp summary to all active partners');
