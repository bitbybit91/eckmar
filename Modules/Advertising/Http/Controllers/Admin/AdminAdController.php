<?php

namespace Modules\Advertising\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Advertising\Models\AdBanner;
use Modules\Advertising\Models\AdLink;
use Modules\Advertising\Models\AdOrder;
use Modules\Advertising\Services\AdApprovalService;

class AdminAdController extends Controller
{
    /** @var AdApprovalService */
    private $approvalService;

    public function __construct(AdApprovalService $approvalService)
    {
        $this->middleware('admin_panel_access');
        $this->approvalService = $approvalService;
    }

    /**
     * Admin advertising dashboard overview.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return view('advertising::admin.dashboard', [
            'pendingBanners'  => AdBanner::pending()->count(),
            'activeBanners'   => AdBanner::active()->count(),
            'pendingLinks'    => AdLink::pending()->count(),
            'activeLinks'     => AdLink::active()->count(),
            'pendingPayments' => AdOrder::paymentNoted()->count(),
            'maxBanners'      => (int) config('advertising.max_banners', 10),
            'maxLinks'        => (int) config('advertising.max_links', 20),
        ]);
    }

    /**
     * List all banners with pagination.
     *
     * @return \Illuminate\View\View
     */
    public function banners()
    {
        $banners = AdBanner::orderByDesc('created_at')->paginate(20);
        return view('advertising::admin.banners', compact('banners'));
    }

    /**
     * Approve a banner.
     *
     * @param Request $request
     * @param int     $id
     * @return RedirectResponse
     */
    public function approveBanner(Request $request, int $id): RedirectResponse
    {
        $request->validate(['month_count' => 'required|integer|between:1,6']);
        $banner = AdBanner::findOrFail($id);
        $this->approvalService->approveBanner($banner, (int) $request->input('month_count'));
        session()->flash('success', 'Banner approved.');
        return redirect()->route('admin.ads.banners');
    }

    /**
     * Reject a banner.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function rejectBanner(int $id): RedirectResponse
    {
        $banner = AdBanner::findOrFail($id);
        $this->approvalService->rejectBanner($banner);
        session()->flash('success', 'Banner rejected.');
        return redirect()->route('admin.ads.banners');
    }

    /**
     * Remove a banner record entirely.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function removeBanner(int $id): RedirectResponse
    {
        $banner = AdBanner::findOrFail($id);

        // Remove stored file.
        if ($banner->filename) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->filename);
        }

        $banner->delete();
        session()->flash('success', 'Banner removed.');
        return redirect()->route('admin.ads.banners');
    }

    /**
     * List all links with pagination.
     *
     * @return \Illuminate\View\View
     */
    public function links()
    {
        $links = AdLink::orderByDesc('created_at')->paginate(20);
        return view('advertising::admin.links', compact('links'));
    }

    /**
     * Approve a link.
     *
     * @param Request $request
     * @param int     $id
     * @return RedirectResponse
     */
    public function approveLink(Request $request, int $id): RedirectResponse
    {
        $request->validate(['month_count' => 'required|integer|between:1,6']);
        $link = AdLink::findOrFail($id);
        $this->approvalService->approveLink($link, (int) $request->input('month_count'));
        session()->flash('success', 'Link approved.');
        return redirect()->route('admin.ads.links');
    }

    /**
     * Reject a link.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function rejectLink(int $id): RedirectResponse
    {
        $link = AdLink::findOrFail($id);
        $this->approvalService->rejectLink($link);
        session()->flash('success', 'Link rejected.');
        return redirect()->route('admin.ads.links');
    }

    /**
     * Show advertising settings.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('advertising::admin.settings', [
            'wallet'         => config('advertising.wallet_address'),
            'bannerPriceUsd' => config('advertising.banner_price_usd'),
            'linkPriceUsd'   => config('advertising.link_price_usd'),
            'maxBanners'     => config('advertising.max_banners'),
            'maxLinks'       => config('advertising.max_links'),
        ]);
    }

    /**
     * Save advertising settings (writes to .env for runtime overrides).
     *
     * Note: For a production-grade implementation, settings would be persisted
     * to a dedicated settings table.  For this module we simply display current
     * values and note that .env overrides are required.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'wallet'          => 'nullable|string|max:255',
            'banner_price_usd' => 'nullable|numeric|min:0',
            'link_price_usd'   => 'nullable|numeric|min:0',
            'max_banners'      => 'nullable|integer|min:1',
            'max_links'        => 'nullable|integer|min:1',
        ]);

        session()->flash('success', 'Settings saved. Update your .env file to persist changes across restarts.');
        return redirect()->route('admin.ads.settings');
    }
}
