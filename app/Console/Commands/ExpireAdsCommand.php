<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Advertising\Models\AdBanner;
use Modules\Advertising\Models\AdLink;

class ExpireAdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark active ad banners and links as expired when their expiry date has passed.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $now = Carbon::now();

        $expiredBanners = AdBanner::active()
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expiredBanners as $banner) {
            $banner->update(['status' => 'expired']);
        }

        $this->info("Expired {$expiredBanners->count()} banner(s).");

        $expiredLinks = AdLink::active()
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expiredLinks as $link) {
            $link->update(['status' => 'expired']);
        }

        $this->info("Expired {$expiredLinks->count()} link(s).");
    }
}
