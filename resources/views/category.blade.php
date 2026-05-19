@extends('master.main')

@section('title', $category -> name . ' category')

@section('seo')
    <meta name="description" content="Browse {{ $category->name }} products on {{ config('app.name') }}.">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ config('app.name') }} - {{ $category->name }} category">
    <meta property="og:description" content="Browse {{ $category->name }} products on {{ config('app.name') }}.">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('img/product.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} - {{ $category->name }} category">
    <meta name="twitter:description" content="Browse {{ $category->name }} products on {{ config('app.name') }}.">
    <meta name="twitter:image" content="{{ asset('img/product.png') }}">
    <link rel="canonical" href="{{ request()->url() }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            @include('includes.categories')
        </div>
        <div class="col-md-9">
            <div class="row">
                <h1 class="col-md-11">{{ $category -> name}}
                    <small>- category</small>
                </h1>
                <div class="col-md-1 text-lg-right">
                    @include('includes.viewpicker')
                </div>
            </div>
            <hr>

            @if($productsView == 'list')
                @foreach($products as $product)
                    @include('includes.product.row', ['product' => $product])
                @endforeach
            @else
                @foreach($products->chunk(3) as $chunks)
                    <div class="row mt-3">
                        @foreach($chunks as $product)
                            <div class="col-md-4 my-md-0 my-2 col-12">
                                @include('includes.product.card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif

            {{ $products -> links('includes.paginate') }}
        </div>

    </div>

@stop
