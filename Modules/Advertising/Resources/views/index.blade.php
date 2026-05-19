@extends('master.main')

@section('title', 'Advertising Marketplace')

@section('content')

    @include('includes.validation')
    @include('includes.flash.success')

    <div class="row mt-3">
        <div class="col-md-12">
            <h2>Advertising Marketplace</h2>
            <p class="text-muted">Reach our anonymous marketplace audience with banner ads or footer links.</p>
            <hr>
        </div>
    </div>

    {{-- Slot counter badges --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <h5>
                Banner Slots
                @if($activeBannerCount < $maxBanners)
                    <span class="badge slot-counter slot-counter--available">{{ $maxBanners - $activeBannerCount }} available</span>
                @else
                    <span class="badge slot-counter slot-counter--full">Full</span>
                @endif
            </h5>
        </div>
        <div class="col-md-6">
            <h5>
                Footer Link Slots
                @if($activeLinkCount < $maxLinks)
                    <span class="badge slot-counter slot-counter--available">{{ $maxLinks - $activeLinkCount }} available</span>
                @else
                    <span class="badge slot-counter slot-counter--full">Full</span>
                @endif
            </h5>
        </div>
    </div>

    {{-- Active banners display --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <h5>Live Banners</h5>
            <div class="ad-slots-grid">
                @forelse($activeBanners as $banner)
                    <div class="ad-banner-slot">
                        <a href="{{ $banner->destination_url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset('storage/' . $banner->filename) }}"
                                 alt="{{ $banner->alt_text }}"
                                 title="{{ $banner->title_text }}"
                                 width="{{ config('advertising.banner_width') }}"
                                 height="{{ config('advertising.banner_height') }}">
                        </a>
                    </div>
                @empty
                    <p class="text-muted">No active banners at this time.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CTAs --}}
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card ad-pricing-card">
                <div class="card-body">
                    <h5>Banner Ad</h5>
                    <p>468×60 GIF, shown on the homepage.</p>
                    <p><strong>${{ number_format(config('advertising.banner_price_usd'), 2) }} / month</strong></p>
                    <a href="{{ route('ads.order.banner') }}" class="btn btn-primary">Order Banner</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card ad-pricing-card">
                <div class="card-body">
                    <h5>Footer Link</h5>
                    <p>Text link in the site footer, visible on every page.</p>
                    <p><strong>${{ number_format(config('advertising.link_price_usd'), 2) }} / month</strong></p>
                    <a href="{{ route('ads.order.link') }}" class="btn btn-secondary">Order Link</a>
                </div>
            </div>
        </div>
    </div>

@stop
