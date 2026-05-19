<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/css/app.css">


    @hasSection('title')
        <title>{{config('app.name')}} - @yield('title')</title>
    @else
        <title>{{config('app.name')}}</title>
    @endif

    @hasSection('seo')
        @yield('seo')
    @else
        <meta name="description" content="{{ config('app.name') }} marketplace">
        <meta name="robots" content="index, follow">
        <meta property="og:title" content="{{ config('app.name') }}">
        <meta property="og:description" content="{{ config('app.name') }} marketplace">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('img/product.png') }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ config('app.name') }}">
        <meta name="twitter:description" content="{{ config('app.name') }} marketplace">
        <meta name="twitter:image" content="{{ asset('img/product.png') }}">
        <link rel="canonical" href="{{ request()->url() }}">
    @endif

</head>
<body class="pb-4">
@include('master.navbar')
@include('master.search')

@hasSection('container-fluid')
    <div class="container-fluid">
@else
    <div class="container">
@endif
        @include('includes.jswarning')
        <div class="mt-4">
            @yield('content')
        </div>


</div>

</body>
</html>
