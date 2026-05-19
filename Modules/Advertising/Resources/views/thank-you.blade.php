@extends('master.main')

@section('title', 'Thank You!')

@section('content')

    <div class="row mt-5">
        <div class="col-md-8 offset-md-2 text-center">
            <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
            <h2>Thank You!</h2>
            <p class="lead">Your payment has been noted and your ad is under review.</p>
            <p class="text-muted">We will review your submission within 24 hours. Once approved, your ad will go live automatically.</p>
            <hr>
            <a href="{{ route('home') }}" class="btn btn-primary">Return to Marketplace</a>
            <a href="{{ route('ads.advertise') }}" class="btn btn-link">Advertise Again</a>
        </div>
    </div>

@stop
