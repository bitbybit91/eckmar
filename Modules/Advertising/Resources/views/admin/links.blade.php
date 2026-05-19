@extends('master.admin')

@section('admin-content')

    <h2 class="mb-3">Footer Link Ads</h2>

    @include('includes.flash.success')

    <a href="{{ route('admin.ads.dashboard') }}" class="btn btn-link btn-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to dashboard
    </a>

    @if($links->isEmpty())
        <p class="text-muted">No link records found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Anchor Text</th>
                    <th>Email</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Months</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($links as $link)
                    <tr>
                        <td>{{ $link->id }}</td>
                        <td>{{ $link->anchor_text }}</td>
                        <td>{{ $link->advertiser_email }}</td>
                        <td>
                            <a href="{{ $link->destination_url }}" target="_blank" rel="noopener">
                                {{ str_limit($link->destination_url, 40) }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $link->status }}">{{ $link->status }}</span>
                        </td>
                        <td>{{ $link->month_count }}</td>
                        <td>{{ $link->expires_at ? $link->expires_at->format('Y-m-d') : '—' }}</td>
                        <td>
                            @if($link->status === 'pending')
                                <form action="{{ route('admin.ads.links.approve', $link->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="month_count" value="{{ $link->month_count }}">
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="{{ route('admin.ads.links.reject', $link->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-warning btn-sm">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $links->links() }}
    @endif

@stop
