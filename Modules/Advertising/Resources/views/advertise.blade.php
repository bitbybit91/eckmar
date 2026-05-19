@extends('master.main')

@section('title', 'Advertise With Us')

@section('content')

    @include('includes.validation')
    @include('includes.flash.success')

    <div class="row mt-3">
        <div class="col-md-12">
            <h2>Advertise With Us</h2>
            <p class="text-muted">All advertising is paid anonymously in Monero (XMR). No accounts required.</p>
            <hr>
        </div>
    </div>

    @if($xmrData['error'])
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            XMR price feed is temporarily unavailable. Prices are indicative only.
        </div>
    @endif

    <div class="row mb-4">

        <div class="col-md-6 mb-3">
            <div class="card ad-pricing-card ad-pricing-card--featured h-100">
                <div class="card-header">
                    <strong>Banner Advertisement</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i>468×60 pixel animated GIF</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Displayed on the homepage</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Up to 6 months</li>
                        <li><i class="fas fa-check text-success mr-2"></i>
                            Slots: {{ $activeBanners }}/{{ $maxBanners }}
                            @if($activeBanners < $maxBanners)
                                <span class="badge slot-counter slot-counter--available">{{ $maxBanners - $activeBanners }} open</span>
                            @else
                                <span class="badge slot-counter slot-counter--full">Full</span>
                            @endif
                        </li>
                    </ul>
                    <p class="mt-3">
                        <span class="h4">${{ number_format($bannerPriceUsd, 2) }}</span>
                        <span class="text-muted">/ month</span>
                    </p>
                    @if($xmrData['price'] > 0)
                        <p class="text-muted small">
                            ≈ {{ number_format($bannerPriceUsd / $xmrData['price'], 6) }} XMR
                            <span class="badge badge-secondary">1 XMR ≈ ${{ number_format($xmrData['price'], 2) }}</span>
                        </p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('ads.order.banner') }}" class="btn btn-primary btn-block">Order Banner</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card ad-pricing-card h-100">
                <div class="card-header">
                    <strong>Footer Text Link</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i>Up to 60-character anchor text</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Shown in footer on every page</li>
                        <li><i class="fas fa-check text-success mr-2"></i>Up to 6 months</li>
                        <li><i class="fas fa-check text-success mr-2"></i>
                            Slots: {{ $activeLinks }}/{{ $maxLinks }}
                            @if($activeLinks < $maxLinks)
                                <span class="badge slot-counter slot-counter--available">{{ $maxLinks - $activeLinks }} open</span>
                            @else
                                <span class="badge slot-counter slot-counter--full">Full</span>
                            @endif
                        </li>
                    </ul>
                    <p class="mt-3">
                        <span class="h4">${{ number_format($linkPriceUsd, 2) }}</span>
                        <span class="text-muted">/ month</span>
                    </p>
                    @if($xmrData['price'] > 0)
                        <p class="text-muted small">
                            ≈ {{ number_format($linkPriceUsd / $xmrData['price'], 6) }} XMR
                            <span class="badge badge-secondary">1 XMR ≈ ${{ number_format($xmrData['price'], 2) }}</span>
                        </p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('ads.order.link') }}" class="btn btn-secondary btn-block">Order Link</a>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <h5>How it works</h5>
            <ol>
                <li>Fill in the order form – no account required.</li>
                <li>Send the exact XMR amount shown to our wallet.</li>
                <li>Click <strong>"I've Sent My Payment"</strong> and we'll review your submission.</li>
                <li>Once approved, your ad goes live immediately.</li>
            </ol>
        </div>
    </div>

@stop
