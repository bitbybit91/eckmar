<?php

namespace Modules\Advertising\Services;

use Carbon\Carbon;
use Modules\Advertising\Models\AdBanner;
use Modules\Advertising\Models\AdLink;
use Modules\Advertising\Models\AdOrder;

class AdApprovalService
{
    /**
     * Approve a banner ad and update its associated order.
     *
     * @param AdBanner $banner
     * @param int      $months
     * @return void
     */
    public function approveBanner(AdBanner $banner, int $months): void
    {
        $banner->approve($months);

        if ($banner->order) {
            $banner->order->update(['status' => 'confirmed']);
        }
    }

    /**
     * Reject a banner ad.
     *
     * @param AdBanner $banner
     * @return void
     */
    public function rejectBanner(AdBanner $banner): void
    {
        $banner->reject();

        if ($banner->order) {
            $banner->order->update(['status' => 'failed']);
        }
    }

    /**
     * Approve a link ad and update its associated order.
     *
     * @param AdLink $link
     * @param int    $months
     * @return void
     */
    public function approveLink(AdLink $link, int $months): void
    {
        $link->approve($months);

        if ($link->order) {
            $link->order->update(['status' => 'confirmed']);
        }
    }

    /**
     * Reject a link ad.
     *
     * @param AdLink $link
     * @return void
     */
    public function rejectLink(AdLink $link): void
    {
        $link->reject();

        if ($link->order) {
            $link->order->update(['status' => 'failed']);
        }
    }

    /**
     * Confirm a payment for an order, making the ad live.
     *
     * @param AdOrder $order
     * @return void
     */
    public function confirmPayment(AdOrder $order): void
    {
        $order->update(['status' => 'confirmed']);
    }

    /**
     * Mark a payment as failed.
     *
     * @param AdOrder $order
     * @return void
     */
    public function failPayment(AdOrder $order): void
    {
        $order->update(['status' => 'failed']);
    }
}
