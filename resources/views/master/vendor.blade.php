@extends('master.main')

@section('title','Vendor - ' . $vendor -> username )

@section('seo')
    <meta name="description" content="View vendor profile for {{ $vendor->username }} on {{ config('app.name') }}.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ config('app.name') }} - Vendor {{ $vendor->username }}">
    <meta property="og:description" content="View vendor profile for {{ $vendor->username }} on {{ config('app.name') }}.">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="profile">
    <meta property="og:image" content="{{ asset('img/product.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} - Vendor {{ $vendor->username }}">
    <meta name="twitter:description" content="View vendor profile for {{ $vendor->username }} on {{ config('app.name') }}.">
    <meta name="twitter:image" content="{{ asset('img/product.png') }}">
    <link rel="canonical" href="{{ request()->url() }}">
@endsection

@section('content')
    {{-- Breadcrumbs --}}
    <nav class="main-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">

            <li class="breadcrumb-item" aria-current="page">{{ config('app.name') }}</li>
            <li class="breadcrumb-item" aria-current="page">Vendor</li>
            <li class="breadcrumb-item active" aria-current="page">{{ $vendor -> username }}</li>
        </ol>
    </nav>



    <div class="row">
        <div class="col-md-12 profile-bg {{$vendor->vendor->getProfileBg()}} rounded pt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    @include('includes.vendor.card')
                </div>
            </div>
        </div>
    </div>

    @include('includes.vendor.stats')
    @yield('vendor-content')
@stop
