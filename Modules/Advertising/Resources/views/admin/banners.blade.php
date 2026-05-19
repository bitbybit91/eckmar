@extends('master.admin')

@section('admin-content')

    <h2 class="mb-3">Banner Ads</h2>

    @include('includes.flash.success')

    <a href="{{ route('admin.ads.dashboard') }}" class="btn btn-link btn-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to dashboard
    </a>

    @if($banners->isEmpty())
        <p class="text-muted">No banner records found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Preview</th>
                    <th>Email</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Months</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($banners as $banner)
                    <tr>
                        <td>{{ $banner->id }}</td>
                        <td>
                            @if($banner->filename)
                                <img src="{{ asset('storage/' . $banner->filename) }}"
                                     alt="{{ $banner->alt_text }}" width="94" height="12" style="max-width:94px;">
                            @endif
                        </td>
                        <td>{{ $banner->advertiser_email }}</td>
                        <td>
                            <a href="{{ $banner->destination_url }}" target="_blank" rel="noopener">
                                {{ str_limit($banner->destination_url, 40) }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $banner->status }}">{{ $banner->status }}</span>
                        </td>
                        <td>{{ $banner->month_count }}</td>
                        <td>{{ $banner->expires_at ? $banner->expires_at->format('Y-m-d') : '—' }}</td>
                        <td>
                            @if($banner->status === 'pending')
                                <form action="{{ route('admin.ads.banners.approve', $banner->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="month_count" value="{{ $banner->month_count }}">
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="{{ route('admin.ads.banners.reject', $banner->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-warning btn-sm">Reject</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.ads.banners.remove', $banner->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this banner permanently?')">
                                @csrf
                                <button class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $banners->links() }}
    @endif

@stop
