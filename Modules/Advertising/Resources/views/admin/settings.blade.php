@extends('master.admin')

@section('admin-content')

    <h2 class="mb-3">Advertising Settings</h2>

    @include('includes.flash.success')
    @include('includes.validation')

    <a href="{{ route('admin.ads.dashboard') }}" class="btn btn-link btn-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to dashboard
    </a>

    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        These values are read from your <code>.env</code> file. Update them there to persist across restarts.
    </div>

    <form action="{{ route('admin.ads.settings.save') }}" method="POST">
        @csrf

        <div class="form-group row">
            <label class="col-md-3 col-form-label">XMR Wallet Address</label>
            <div class="col-md-9">
                <input type="text" name="wallet" class="form-control"
                       value="{{ old('wallet', $wallet) }}" placeholder="XMR_WALLET_ADDRESS">
                <small class="form-text text-muted">Set <code>XMR_WALLET_ADDRESS</code> in .env</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 col-form-label">Banner Price (USD/month)</label>
            <div class="col-md-9">
                <input type="number" step="0.01" name="banner_price_usd" class="form-control"
                       value="{{ old('banner_price_usd', $bannerPriceUsd) }}">
                <small class="form-text text-muted">Set <code>AD_BANNER_PRICE_USD</code> in .env</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 col-form-label">Link Price (USD/month)</label>
            <div class="col-md-9">
                <input type="number" step="0.01" name="link_price_usd" class="form-control"
                       value="{{ old('link_price_usd', $linkPriceUsd) }}">
                <small class="form-text text-muted">Set <code>AD_LINK_PRICE_USD</code> in .env</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 col-form-label">Max Active Banners</label>
            <div class="col-md-9">
                <input type="number" name="max_banners" class="form-control"
                       value="{{ old('max_banners', $maxBanners) }}">
                <small class="form-text text-muted">Set <code>MAX_ACTIVE_BANNERS</code> in .env</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 col-form-label">Max Active Links</label>
            <div class="col-md-9">
                <input type="number" name="max_links" class="form-control"
                       value="{{ old('max_links', $maxLinks) }}">
                <small class="form-text text-muted">Set <code>MAX_ACTIVE_LINKS</code> in .env</small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

@stop
