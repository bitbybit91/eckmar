@extends('master.main')

@section('title', 'Complete Payment')

@section('content')

    <div class="row mt-3">
        <div class="col-md-8 offset-md-2">

            <h3>Complete Your XMR Payment</h3>
            <p class="text-muted">Send the exact amount below to the wallet address to submit your ad for review.</p>

            @if($xmrData['error'])
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    XMR price feed is temporarily unavailable. The amount shown was calculated at order time.
                </div>
            @endif

            <div class="xmr-payment-card mt-4 mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1 text-muted">Amount due</p>
                        <div class="xmr-amount-value" id="xmr-amount">{{ number_format($xmrAmount, 6) }} XMR</div>
                        <button class="btn btn-outline-secondary btn-sm mt-1" id="copy-amount">
                            <i class="fas fa-copy mr-1"></i>Copy Amount
                        </button>

                        <p class="mt-3 mb-1 text-muted">Send to wallet</p>
                        <code class="wallet-address-code" id="wallet-address">{{ $wallet }}</code>
                        <br>
                        <button class="btn btn-outline-secondary btn-sm mt-1" id="copy-wallet">
                            <i class="fas fa-copy mr-1"></i>Copy Address
                        </button>
                    </div>
                    <div class="col-md-4 text-center">
                        <div id="xmr-qr-container">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($moneroUri) }}"
                                 alt="Monero QR Code" width="160" height="160">
                        </div>
                        <small class="text-muted d-block mt-1">Scan with Monero wallet</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                After sending, click the button below. Your ad will be reviewed within 24 hours.
            </div>

            <form action="{{ route('ads.pay.noted') }}" method="POST" id="pay-noted-form">
                @csrf
                <input type="hidden" name="ref" value="{{ $order->id }}">
                <button type="button" class="btn btn-success btn-lg" id="payment-sent-btn">
                    <i class="fas fa-check mr-2"></i>I've Sent My Payment
                </button>
                <a href="{{ route('ads.advertise') }}" class="btn btn-link">Cancel</a>
            </form>

        </div>
    </div>

    {{-- Confirmation modal --}}
    <div class="modal fade" id="payConfirmModal" tabindex="-1" role="dialog" aria-labelledby="payConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payConfirmModalLabel">Confirm Payment Submission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Have you <strong>already sent</strong> the exact XMR amount to the wallet?
                    <br><br>
                    Submitting without sending will delay your ad approval.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, go back</button>
                    <button type="button" class="btn btn-success" id="confirm-submit">Yes, submit</button>
                </div>
            </div>
        </div>
    </div>

@stop

@push('scripts')
<script>
window.XMR_RATE       = {{ json_encode($xmrData['price']) }};
window.XMR_MONERO_URI = {{ json_encode($moneroUri) }};
</script>
<script src="{{ asset('js/advertising.js') }}"></script>
@endpush
