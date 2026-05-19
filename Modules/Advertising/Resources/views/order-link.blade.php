@extends('master.main')

@section('title', 'Order Footer Link')

@section('content')

    @include('includes.validation')
    @include('includes.flash.success')

    <div class="row mt-3">
        <div class="col-md-8 offset-md-2">
            <h3>Order a Footer Link</h3>
            <p class="text-muted">Up to 60-character text link in the site footer. Priced at
                <strong>${{ number_format(config('advertising.link_price_usd'), 2) }}</strong> / month.</p>
            <hr>

            <form action="{{ route('ads.order.link.post') }}" method="POST" id="link-order-form">
                @csrf

                <div class="form-group">
                    <label for="advertiser_email">Your e-mail address <span class="text-danger">*</span></label>
                    <input type="email" name="advertiser_email" id="advertiser_email"
                           class="form-control @error('advertiser_email') is-invalid @enderror"
                           value="{{ old('advertiser_email') }}" required>
                    <small class="form-text text-muted">Used only for ad status notifications.</small>
                </div>

                <div class="form-group">
                    <label for="destination_url">Destination URL <span class="text-danger">*</span></label>
                    <input type="url" name="destination_url" id="destination_url"
                           class="form-control @error('destination_url') is-invalid @enderror"
                           value="{{ old('destination_url') }}" required>
                </div>

                <div class="form-group">
                    <label for="anchor_text">
                        Anchor text <span class="text-danger">*</span>
                        <small class="text-muted">(<span id="char-count">0</span>/60 characters)</small>
                    </label>
                    <input type="text" name="anchor_text" id="anchor_text"
                           class="form-control @error('anchor_text') is-invalid @enderror"
                           value="{{ old('anchor_text') }}" maxlength="60" required>
                    <small class="form-text text-muted">Plain text only – HTML will be stripped.</small>
                </div>

                <div class="form-group">
                    <label for="month_count">Duration <span class="text-danger">*</span></label>
                    <select name="month_count" id="month_count"
                            class="form-control @error('month_count') is-invalid @enderror">
                        @for($m = 1; $m <= 6; $m++)
                            <option value="{{ $m }}" {{ old('month_count', 1) == $m ? 'selected' : '' }}>
                                {{ $m }} {{ $m === 1 ? 'month' : 'months' }}
                                — ${{ number_format(config('advertising.link_price_usd') * $m, 2) }}
                                @if($xmrData['price'] > 0)
                                    ≈ {{ number_format((config('advertising.link_price_usd') * $m) / $xmrData['price'], 6) }} XMR
                                @endif
                            </option>
                        @endfor
                    </select>
                    <small class="form-text text-muted" id="price-preview"></small>
                </div>

                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-arrow-right mr-2"></i>Continue to Payment
                </button>
                <a href="{{ route('ads.advertise') }}" class="btn btn-link">Back</a>
            </form>
        </div>
    </div>

@stop

@push('scripts')
<script>
window.XMR_RATE = {{ json_encode($xmrData['price']) }};
window.LINK_PRICE_USD = {{ json_encode((float) config('advertising.link_price_usd')) }};
</script>
<script src="{{ asset('js/advertising.js') }}"></script>
@endpush
