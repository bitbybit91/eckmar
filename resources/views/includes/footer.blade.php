<footer class="mt-5 py-3 border-top text-muted small">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <strong>Our Advertisers:</strong>
                @php
                    $footerAdLinks = \Modules\Advertising\Models\AdLink::active()->get();
                @endphp
                @if($footerAdLinks->isEmpty())
                    <a href="{{ route('ads.order.link') }}" class="text-muted">
                        Footer links available — ${{ number_format(config('advertising.link_price_usd', 100), 0) }}/month
                    </a>
                @else
                    @foreach($footerAdLinks as $adLink)
                        <a href="{{ $adLink->destination_url }}" target="_blank" rel="noopener noreferrer"
                           class="mr-3 text-muted">{{ $adLink->anchor_text }}</a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</footer>
