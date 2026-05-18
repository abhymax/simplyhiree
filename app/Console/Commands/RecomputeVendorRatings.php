<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\VendorRating;
use Illuminate\Console\Command;

class RecomputeVendorRatings extends Command
{
    protected $signature = 'vendors:recompute-ratings';

    protected $description = 'Recompute every partner-user vendor rating aggregate (avg, selection ratio, closure rate, repeat hires, badge, level).';

    public function handle(): int
    {
        $this->info('['.now()->toDateTimeString().'] Vendor ratings sweep started');
        $touched = 0;
        User::role('partner')->whereNull('parent_partner_id')->select(['id'])->chunkById(200, function ($chunk) use (&$touched) {
            foreach ($chunk as $u) {
                VendorRating::recomputeFor($u->id);
                $touched++;
            }
        });
        $this->info("Vendor ratings sweep done. Touched {$touched} partner accounts.");
        return self::SUCCESS;
    }
}
