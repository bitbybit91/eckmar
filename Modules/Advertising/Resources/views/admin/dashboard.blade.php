@extends('master.admin')

@section('admin-content')

    <h2 class="mb-3">Advertising Dashboard</h2>

    @include('includes.flash.success')

    <div class="card-columns">

        <div class="card text-center">
            <div class="card-body">
                <h1>{{ $pendingBanners }}</h1>
            </div>
            <div class="card-footer">
                Banners awaiting approval
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <h1>{{ $activeBanners }} / {{ $maxBanners }}</h1>
            </div>
            <div class="card-footer">
                Active banner slots
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <h1>{{ $pendingLinks }}</h1>
            </div>
            <div class="card-footer">
                Links awaiting approval
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <h1>{{ $activeLinks }} / {{ $maxLinks }}</h1>
            </div>
            <div class="card-footer">
                Active link slots
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <h1>{{ $pendingPayments }}</h1>
            </div>
            <div class="card-footer">
                Payments noted (awaiting confirmation)
            </div>
        </div>

    </div>

    <div class="mt-4">
        <a href="{{ route('admin.ads.banners') }}" class="btn btn-primary mr-2">
            <i class="fas fa-image mr-1"></i>Manage Banners
        </a>
        <a href="{{ route('admin.ads.links') }}" class="btn btn-secondary mr-2">
            <i class="fas fa-link mr-1"></i>Manage Links
        </a>
        <a href="{{ route('admin.ads.payments') }}" class="btn btn-info mr-2">
            <i class="fas fa-coins mr-1"></i>Manage Payments
        </a>
        <a href="{{ route('admin.ads.settings') }}" class="btn btn-light">
            <i class="fas fa-cog mr-1"></i>Settings
        </a>
    </div>

@stop
