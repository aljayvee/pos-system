@extends('admin.layout')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-success bg-opacity-10 text-dark border-0 py-3">
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Record Payment
                        </h5>
                    </div>

                    <form action="{{ route('credits.pay', $credit->credit_id) }}" method="POST">
                        @csrf
                        <div class="card-body p-4">

                            {{-- Customer Info Card --}}
                            <div
                                class="d-flex align-items-center justify-content-between mb-4 bg-light p-3 rounded-4 border border-light">
                                <div>
                                    <small class="text-secondary text-uppercase d-block fw-bold small">Customer</small>
                                    <span class="fs-6 text-dark fw-bold">{{ $credit->customer->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="text-end">
                                    <small class="text-secondary text-uppercase d-block fw-bold small">Balance Due</small>
                                    <span
                                        class="fs-4 text-danger fw-bold">₱{{ number_format($credit->remaining_balance, 2) }}</span>
                                </div>
                            </div>

                            {{-- Payment Amount --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Payment Amount</label>
                                <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden">
                                    <span class="input-group-text bg-white border-0 fw-bold text-secondary ps-3">₱</span>
                                    <input type="number" name="amount" class="form-control border-0 fw-bold text-dark fs-4"
                                        max="{{ $credit->remaining_balance }}" step="0.01" required placeholder="0.00"
                                        autofocus>
                                </div>
                                <div class="form-text small mt-2 ms-2 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Enter amount collected from customer.
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Notes
                                    (Optional)</label>
                                <textarea name="notes" class="form-control bg-light border-0 rounded-3" rows="3"
                                    placeholder="e.g. Paid via Gcash, Partial payment"></textarea>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-grid gap-3 d-md-flex justify-content-md-end">
                                <a href="{{ route('credits.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                                    Confirm Payment
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection