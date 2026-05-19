<?php

namespace Modules\Advertising\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Advertising\Models\AdOrder;
use Modules\Advertising\Services\AdApprovalService;

class AdminPaymentController extends Controller
{
    /** @var AdApprovalService */
    private $approvalService;

    public function __construct(AdApprovalService $approvalService)
    {
        $this->middleware('admin_panel_access');
        $this->approvalService = $approvalService;
    }

    /**
     * List all ad orders with pagination.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $payments = AdOrder::orderByDesc('created_at')->paginate(20);
        return view('advertising::admin.payments', compact('payments'));
    }

    /**
     * Confirm a payment, marking the order as confirmed.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function confirm(string $id): RedirectResponse
    {
        $order = AdOrder::findOrFail($id);
        $this->approvalService->confirmPayment($order);
        session()->flash('success', 'Payment confirmed.');
        return redirect()->route('admin.ads.payments');
    }

    /**
     * Mark a payment as failed.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function fail(string $id): RedirectResponse
    {
        $order = AdOrder::findOrFail($id);
        $this->approvalService->failPayment($order);
        session()->flash('success', 'Payment marked as failed.');
        return redirect()->route('admin.ads.payments');
    }
}
