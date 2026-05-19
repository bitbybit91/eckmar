@extends('master.main')

@section('title', 'Order Banner Ad')

@section('content')

    @include('includes.validation')
    @include('includes.flash.success')

    <div class="row mt-3">
        <div class="col-md-8 offset-md-2">
            <h3>Order a Banner Ad</h3>
            <p class="text-muted">468×60 pixel GIF — displayed on the homepage. Priced at
                <strong>${{ number_format(config('advertising.banner_price_usd'), 2) }}</strong> / month.</p>
            <hr>

            <form action="{{ route('ads.order.banner.post') }}" method="POST" enctype="multipart/form-data"
                  id="banner-order-form">
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
                    <label for="alt_text">Alt text <span class="text-danger">*</span></label>
                    <input type="text" name="alt_text" id="alt_text"
                           class="form-control @error('alt_text') is-invalid @enderror"
                           value="{{ old('alt_text') }}" maxlength="125" required>
                </div>

                <div class="form-group">
                    <label for="title_text">Title text <span class="text-muted">(optional)</span></label>
                    <input type="text" name="title_text" id="title_text"
                           class="form-control @error('title_text') is-invalid @enderror"
                           value="{{ old('title_text') }}" maxlength="125">
                </div>

                <div class="form-group">
                    <label for="month_count">Duration <span class="text-danger">*</span></label>
                    <select name="month_count" id="month_count"
                            class="form-control @error('month_count') is-invalid @enderror">
                        @for($m = 1; $m <= 6; $m++)
                            <option value="{{ $m }}" {{ old('month_count', 1) == $m ? 'selected' : '' }}>
                                {{ $m }} {{ $m === 1 ? 'month' : 'months' }}
                                — ${{ number_format(config('advertising.banner_price_usd') * $m, 2) }}
                                @if($xmrData['price'] > 0)
                                    ≈ {{ number_format((config('advertising.banner_price_usd') * $m) / $xmrData['price'], 6) }} XMR
                                @endif
                            </option>
                        @endfor
                    </select>
                    <small class="form-text text-muted" id="price-preview"></small>
                </div>

                <div class="form-group">
                    <label for="banner_file">Banner GIF file <span class="text-danger">*</span></label>
                    <input type="file" name="banner_file" id="banner_file" accept="image/gif"
                           class="form-control-file @error('banner_file') is-invalid @enderror" required>
                    <small class="form-text text-muted">
                        Must be exactly 468×60 pixels, GIF format, max 2 MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
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
window.BANNER_PRICE_USD = {{ json_encode((float) config('advertising.banner_price_usd')) }};
</script>
<script src="{{ asset('js/advertising.js') }}"></script>
@endpush
