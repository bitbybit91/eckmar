<?php

namespace Modules\Advertising\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Advertising\Http\Requests\StoreBannerOrderRequest;
use Modules\Advertising\Http\Requests\StoreLinkOrderRequest;
use Modules\Advertising\Models\AdBanner;
use Modules\Advertising\Models\AdLink;
use Modules\Advertising\Models\AdOrder;
use Modules\Advertising\Services\XmrPriceService;

class PublicAdController extends Controller
{
    /** @var XmrPriceService */
    private $xmrPriceService;

    public function __construct(XmrPriceService $xmrPriceService)
    {
        $this->xmrPriceService = $xmrPriceService;
    }

    /**
     * Display the advertising landing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $activeBanners      = AdBanner::active()->get();
        $activeLinks        = AdLink::active()->get();
        $activeBannerCount  = $activeBanners->count();
        $activeLinkCount    = $activeLinks->count();
        $maxBanners         = (int) config('advertising.max_banners', 10);
        $maxLinks           = (int) config('advertising.max_links', 20);

        return view('advertising::index', compact(
            'activeBanners',
            'activeLinks',
            'activeBannerCount',
            'activeLinkCount',
            'maxBanners',
            'maxLinks'
        ));
    }

    /**
     * Show the advertising information / pricing page.
     *
     * @return \Illuminate\View\View
     */
    public function advertise()
    {
        $xmrData = $this->xmrPriceService->fetchPrice();

        return view('advertising::advertise', [
            'xmrData'         => $xmrData,
            'bannerPriceUsd'  => (float) config('advertising.banner_price_usd', 200),
            'linkPriceUsd'    => (float) config('advertising.link_price_usd', 100),
            'maxBanners'      => (int) config('advertising.max_banners', 10),
            'maxLinks'        => (int) config('advertising.max_links', 20),
            'activeBanners'   => AdBanner::active()->count(),
            'activeLinks'     => AdLink::active()->count(),
        ]);
    }

    /**
     * Show the banner order form.
     *
     * @return \Illuminate\View\View
     */
    public function orderBannerForm()
    {
        $xmrData      = $this->xmrPriceService->fetchPrice();
        $bannerPrice  = (float) config('advertising.banner_price_usd', 200);
        $xmrRate      = $xmrData['price'] > 0 ? $xmrData['price'] : 1;
        $xmrAmount    = $xmrRate > 0 ? round($bannerPrice / $xmrRate, (int) config('advertising.xmr_precision', 6)) : 0;

        return view('advertising::order-banner', compact('xmrData', 'bannerPrice', 'xmrAmount'));
    }

    /**
     * Handle the banner order form submission.
     *
     * @param StoreBannerOrderRequest $request
     * @return RedirectResponse
     */
    public function orderBannerPost(StoreBannerOrderRequest $request): RedirectResponse
    {
        $xmrData  = $this->xmrPriceService->fetchPrice();
        $xmrRate  = $xmrData['price'] > 0 ? $xmrData['price'] : 1;
        $usdAmt   = (float) config('advertising.banner_price_usd', 200) * (int) $request->input('month_count');
        $xmrAmt   = $xmrRate > 0 ? round($usdAmt / $xmrRate, (int) config('advertising.xmr_precision', 6)) : 0;
        $orderId  = 'ord_' . bin2hex(random_bytes(14));
        $wallet   = (string) config('advertising.wallet_address', '');

        // Create the order first (banner FK points to it).
        $order = AdOrder::create([
            'id'         => $orderId,
            'type'       => 'banner',
            'item_id'    => 0, // placeholder – updated after banner is created
            'usd_amount' => $usdAmt,
            'xmr_amount' => $xmrAmt,
            'xmr_rate'   => $xmrRate,
            'wallet'     => $wallet,
            'status'     => 'awaiting_payment',
        ]);

        // Store the banner record.
        $banner = AdBanner::create([
            'status'           => 'pending',
            'advertiser_email' => $request->input('advertiser_email'),
            'destination_url'  => $request->input('destination_url'),
            'alt_text'         => $request->input('alt_text'),
            'title_text'       => $request->input('title_text'),
            'filename'         => '', // set after file move
            'order_id'         => $orderId,
            'month_count'      => (int) $request->input('month_count'),
        ]);

        // Move the uploaded GIF to permanent storage.
        $file     = $request->file('banner_file');
        $filename = $banner->id . '.gif';
        $file->storeAs('public/ad-banners', $filename);

        $banner->update([
            'filename' => 'ad-banners/' . $filename,
        ]);

        // Update the order's item_id now that we have the banner id.
        $order->update(['item_id' => $banner->id]);

        return redirect()->route('ads.pay', ['ref' => $orderId]);
    }

    /**
     * Show the link order form.
     *
     * @return \Illuminate\View\View
     */
    public function orderLinkForm()
    {
        $xmrData    = $this->xmrPriceService->fetchPrice();
        $linkPrice  = (float) config('advertising.link_price_usd', 100);
        $xmrRate    = $xmrData['price'] > 0 ? $xmrData['price'] : 1;
        $xmrAmount  = $xmrRate > 0 ? round($linkPrice / $xmrRate, (int) config('advertising.xmr_precision', 6)) : 0;

        return view('advertising::order-link', compact('xmrData', 'linkPrice', 'xmrAmount'));
    }

    /**
     * Handle the link order form submission.
     *
     * @param StoreLinkOrderRequest $request
     * @return RedirectResponse
     */
    public function orderLinkPost(StoreLinkOrderRequest $request): RedirectResponse
    {
        $xmrData  = $this->xmrPriceService->fetchPrice();
        $xmrRate  = $xmrData['price'] > 0 ? $xmrData['price'] : 1;
        $usdAmt   = (float) config('advertising.link_price_usd', 100) * (int) $request->input('month_count');
        $xmrAmt   = $xmrRate > 0 ? round($usdAmt / $xmrRate, (int) config('advertising.xmr_precision', 6)) : 0;
        $orderId  = 'ord_' . bin2hex(random_bytes(14));
        $wallet   = (string) config('advertising.wallet_address', '');

        // Sanitise anchor text: strip HTML tags.
        $anchorText = strip_tags($request->input('anchor_text'));
        $anchorText = htmlspecialchars($anchorText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $order = AdOrder::create([
            'id'         => $orderId,
            'type'       => 'link',
            'item_id'    => 0,
            'usd_amount' => $usdAmt,
            'xmr_amount' => $xmrAmt,
            'xmr_rate'   => $xmrRate,
            'wallet'     => $wallet,
            'status'     => 'awaiting_payment',
        ]);

        $link = AdLink::create([
            'status'           => 'pending',
            'advertiser_email' => $request->input('advertiser_email'),
            'destination_url'  => $request->input('destination_url'),
            'anchor_text'      => $anchorText,
            'order_id'         => $orderId,
            'month_count'      => (int) $request->input('month_count'),
        ]);

        $order->update(['item_id' => $link->id]);

        return redirect()->route('ads.pay', ['ref' => $orderId]);
    }

    /**
     * Show the payment page for a given order.
     *
     * @param Request $request
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function pay(Request $request)
    {
        $orderId = $request->query('ref');
        $order   = AdOrder::find($orderId);

        if ($order === null) {
            return redirect()->route('ads.advertise')->with('errormessage', 'Order not found.');
        }

        $xmrData    = $this->xmrPriceService->fetchPrice();
        $wallet     = $order->wallet;
        $xmrAmount  = $order->xmr_amount;
        $moneroUri  = 'monero:' . $wallet . '?tx_amount=' . $xmrAmount;

        return view('advertising::pay', compact('order', 'xmrData', 'wallet', 'xmrAmount', 'moneroUri'));
    }

    /**
     * Mark an order's payment as noted.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function payNoted(Request $request): RedirectResponse
    {
        $orderId = $request->input('ref');
        $order   = AdOrder::find($orderId);

        if ($order !== null && $order->status === 'awaiting_payment') {
            $order->update([
                'status'   => 'payment_noted',
                'noted_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('ads.thankyou');
    }

    /**
     * Show the thank-you page.
     *
     * @return \Illuminate\View\View
     */
    public function thankYou()
    {
        return view('advertising::thank-you');
    }
}
