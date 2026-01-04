@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-0 py-md-4">

        {{-- MOBILE HEADER --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
            <div class="px-3 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('transactions.index') }}" class="text-dark"><i
                            class="fas fa-arrow-left fa-lg"></i></a>
                    <h6 class="m-0 fw-bold text-dark">Receipt #{{ $sale->id }}</h6>
                </div>

            </div>
        </div>

        {{-- TOP NAV (DESKTOP) --}}
        <div class="d-none d-lg-flex align-items-center justify-content-between p-3">
            <a href="{{ route('transactions.index') }}" class="btn btn-light shadow-sm rounded-circle border-0"
                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left text-dark"></i>
            </a>
            <h6 class="mb-0 fw-bold text-uppercase letter-spacing-1">Receipt #{{ $sale->id }}</h6>
            <div style="width: 40px;"></div> {{-- Spacer for centering --}}
        </div>

        <div class="row justify-content-center m-0 pb-5 mb-5 px-3 px-md-0 pt-3 pt-md-0">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5 px-0 px-sm-3">

                {{-- RECEIPT CARD --}}
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 position-relative">

                    {{-- Status Stamp (Visual Flair) --}}
                    <div class="position-absolute top-0 end-0 mt-3 me-3 opacity-25"
                        style="transform: rotate(15deg); border: 4px solid {{ $sale->payment_method == 'credit' ? 'red' : 'green' }}; color: {{ $sale->payment_method == 'credit' ? 'red' : 'green' }}; padding: 5px 15px; border-radius: 8px; font-weight: 900; font-size: 1.5rem; pointer-events: none;">
                        {{ $sale->payment_method == 'credit' ? 'CREDIT' : 'PAID' }}
                    </div>

                    {{-- RECEIPT HEADER --}}
                    <div class="bg-dark text-white p-4 text-center">
                        <div class="bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm"
                            style="width: 64px; height: 64px; font-size: 1.5rem;">
                            <i class="fas fa-store"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ config('app.name', 'POS System') }}</h4>
                        <p class="text-white-50 small mb-0">{{ $sale->created_at->format('F d, Y • h:i A') }}</p>
                    </div>

                    {{-- RECEIPT BODY --}}
                    <div class="card-body p-4 bg-white position-relative">
                        {{-- Customer Info --}}
                        <div class="text-center mb-4 pb-3 border-bottom border-dashed">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Customer</small>
                            <h5 class="fw-bold text-dark mt-1">{{ $sale->customer->name ?? 'Walk-in Customer' }}</h5>
                            <div class="badge bg-light text-dark border mt-1">Cashier: {{ $sale->user->name }}</div>
                        </div>

                        {{-- Items --}}
                        <div class="mb-4">
                            @foreach($sale->saleItems as $item)
                                @php
                                    $returnedRecords = \App\Models\SalesReturn::where('sale_id', $sale->id)->where('product_id', $item->product_id)->get();
                                    $totalReturned = $returnedRecords->sum('quantity');
                                    $isReturned = $totalReturned > 0;
                                @endphp
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 {{ $isReturned ? 'opacity-50' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-light rounded px-2 py-1 me-3 fw-bold small border text-center"
                                            style="min-width: 40px;">
                                            {{ $item->quantity }}x
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->product->name ?? 'Unknown Item' }}</div>
                                            <small class="text-muted">@ ₱{{ number_format($item->price, 2) }}</small>
                                            @foreach($returnedRecords as $return)
                                                <div class="mt-1">
                                                    @if($return->condition == 'good')
                                                        <span class="badge bg-success-subtle text-success border border-success px-2">
                                                            <i class="fas fa-check-circle me-1"></i> Returned (Good):
                                                            {{ $return->quantity }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2">
                                                            <i class="fas fa-times-circle me-1"></i> Returned (Damaged):
                                                            {{ $return->quantity }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark">₱{{ number_format($item->price * $item->quantity, 2) }}</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="bg-light p-3 rounded-3">
                            @if($sale->points_discount > 0)
                                <div class="d-flex justify-content-between text-success mb-2 small">
                                    <span>Points Discount</span>
                                    <span>-₱{{ number_format($sale->points_discount, 2) }}</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-muted text-uppercase">Total Amount</span>
                                <span
                                    class="fw-bolder text-primary display-6">₱{{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Receipt Zig-Zag Bottom Effect (CSS Trick) --}}
                        <div style="
                            position: absolute;
                            bottom: -10px;
                            left: 0;
                            width: 100%;
                            height: 20px;
                            background: linear-gradient(135deg, white 10px, transparent 0) 0 10px,
                                        linear-gradient(225deg, white 10px, transparent 0) 0 10px;
                            background-size: 20px 20px;
                            background-repeat: repeat-x;
                        "></div>
                    </div>

                    {{-- Action Footer (Desktop Only - Mobile is Sticky) --}}
                    <div class="card-footer bg-white border-0 p-4 d-none d-md-block pt-5">
                        <div class="row g-2">
                            <div class="col-4">
                                <a href="{{ route('transactions.print', $sale->id) }}" target="_blank"
                                    class="btn btn-dark w-100 py-2 rounded-3">
                                    <i class="fas fa-print me-2"></i> Print
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('admin.transactions.return', $sale->id) }}"
                                    class="btn btn-outline-warning w-100 py-2 rounded-3">
                                    <i class="fas fa-undo me-2"></i> Refund
                                </a>
                            </div>
                            <div class="col-4">
                                <form action="{{ route('transactions.destroy', $sale->id) }}" method="POST"
                                    onsubmit="return confirm('VOID TRANSACTION?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 py-2 rounded-3">
                                        <i class="fas fa-ban me-2"></i> Void
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MOBILE STATIC BUTTONS (Scrollable) --}}
                <div class="d-md-none mt-3 pb-5">
                    <div class="row g-2">
                        <div class="col-3">
                            <a href="{{ route('admin.transactions.return', $sale->id) }}"
                                class="btn btn-light w-100 py-3 rounded-4 text-secondary border d-flex flex-column align-items-center justify-content-center h-100 shadow-sm">
                                <i class="fas fa-undo mb-2 fa-lg"></i> <small style="font-size: 0.65rem;"
                                    class="fw-bold">Return</small>
                            </a>
                        </div>
                        <div class="col-3">
                            <form action="{{ route('transactions.destroy', $sale->id) }}" method="POST" class="h-100"
                                onsubmit="return confirm('CRITICAL WARNING:\n\nThis will VOID the entire transaction:\n- Restore stock\n- Remove sales record\n- Cancel credit/points\n\nAre you sure?');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="btn btn-light w-100 py-3 rounded-4 text-danger border d-flex flex-column align-items-center justify-content-center h-100 shadow-sm">
                                    <i class="fas fa-ban mb-2 fa-lg"></i> <small style="font-size: 0.65rem;"
                                        class="fw-bold">Void</small>
                                </button>
                            </form>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('transactions.print', $sale->id) }}" target="_blank"
                                class="btn btn-dark w-100 py-3 rounded-4 h-100 d-flex align-items-center justify-content-center shadow-lg">
                                <i class="fas fa-print me-2"></i> <span class="fw-bold">Print Receipt</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


@endsection