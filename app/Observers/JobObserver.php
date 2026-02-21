<?php

namespace App\Observers;

use App\Models\Job;
use App\Services\SuperadminActivityService;

class JobObserver
{
    public function created(Job $job): void
    {
        $job->loadMissing('user');

        if (!$job->user || !$job->user->hasRole('client')) {
            return;
        }

        app(SuperadminActivityService::class)->logClientJobPosted($job);
    }
}
