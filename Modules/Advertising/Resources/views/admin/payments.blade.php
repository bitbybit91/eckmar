@extends('master.admin')

@section('admin-content')

    <h2 class="mb-3">Ad Payments</h2>

    @include('includes.flash.success')

    <a href="{{ route('admin.ads.dashboard') }}" class="btn btn-link btn-sm mb-3">
        <i class="fas fa-arrow-left mr-1"></i>Back to dashboard
    </a>

    @if($payments->isEmpty())
        <p class="text-muted">No payment records found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Type</th>
                    <th>USD</th>
                    <th>XMR</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Noted at</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td><code>{{ $payment->id }}</code></td>
                        <td>
                            <span class="badge badge-info">{{ $payment->type }}</span>
                        </td>
                        <td>${{ number_format($payment->usd_amount, 2) }}</td>
                        <td>{{ number_format($payment->xmr_amount, 6) }}</td>
                        <td>${{ number_format($payment->xmr_rate, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'confirmed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>{{ $payment->noted_at ? $payment->noted_at->format('Y-m-d H:i') : '—' }}</td>
                        <td>
                            @if(in_array($payment->status, ['payment_noted', 'awaiting_payment']))
                                <form action="{{ route('admin.ads.payments.confirm', $payment->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Confirm</button>
                                </form>
                                <form action="{{ route('admin.ads.payments.fail', $payment->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Fail</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $payments->links() }}
    @endif

@stop
