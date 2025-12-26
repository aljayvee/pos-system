@extends('admin.layout')

@section('title', 'Register Cash Logs')

@section('content')
<div class="row">
    <div class="col-12">
        
        {{-- SECTION 1: PENDING ADJUSTMENTS (Action Items) --}}
        @if($adjustments->isNotEmpty())
        <div class="card shadow-sm border-0 mb-4 bg-warning bg-opacity-10">
            <div class="card-header bg-warning text-dark py-3 d-flex align-items-center justify-content-between border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle me-2"></i>Action Required</h6>
                <span class="badge bg-white text-dark rounded-pill">{{ $adjustments->count() }} Request(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($adjustments as $adj)
                    <div class="list-group-item bg-transparent p-3 border-bottom border-warning border-opacity-25">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 32px; height: 32px;">
                                    {{ substr($adj->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold lh-1">{{ $adj->user->name }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem">Session #{{ $adj->cash_register_session_id }}</small>
                                </div>
                            </div>
                            <span class="badge bg-white text-danger border border-danger fw-bold rounded-pill">
                                Variance: ₱{{ number_format($adj->new_amount - $adj->original_amount, 2) }}
                            </span>
                        </div>
                        
                        <div class="bg-white rounded p-2 mb-2 border border-warning border-opacity-25">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Expected:</span>
                                <span class="fw-bold">₱{{ number_format($adj->original_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Counted:</span>
                                <span class="fw-bold text-dark">₱{{ number_format($adj->new_amount, 2) }}</span>
                            </div>
                            <div class="mt-1 small text-secondary fst-italic">"{{ $adj->reason }}"</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button onclick="approveAdjustment({{ $adj->id }}, 'approve')" class="btn btn-sm btn-success flex-fill fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                            <button onclick="approveAdjustment({{ $adj->id }}, 'reject')" class="btn btn-sm btn-light text-danger flex-fill fw-bold rounded-pill border">
                                Reject
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- SECTION 2: SESSION HISTORY LOGS (Mobile Timeline) --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0 sticky-top shadow-sm" style="z-index: 10;">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Register History</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($logs as $log)
                    <div class="list-group-item p-3 border-bottom-0 border-top bg-white hover-bg-light transition-all">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-cash-register"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $log->closed_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->closed_at->format('h:i A') }} • {{ $log->user->name }}</small>
                                </div>
                            </div>
                            @if(abs($log->variance) > 0.01)
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold">
                                    <i class="fas fa-exclamation-triangle me-1"></i> {{ number_format($log->variance, 2) }}
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold">
                                    <i class="fas fa-check-circle me-1"></i> Balanced
                                </span>
                            @endif
                        </div>
                        
                        {{-- Collapsible Details (Simple expansion) --}}
                        <div class="bg-light rounded-3 p-3 mt-2">
                            <div class="row g-2 text-center">
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Opening</small>
                                    <div class="fw-bold text-dark">₱{{ number_format($log->opening_amount, 2) }}</div>
                                </div>
                                <div class="col-4 border-end">
                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Expected</small>
                                    <div class="fw-bold text-primary">₱{{ number_format($log->expected_amount, 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Closing</small>
                                    <div class="fw-bold text-success">₱{{ number_format($log->closing_amount, 2) }}</div>
                                </div>
                            </div>
                            @if($log->notes)
                                <div class="mt-2 pt-2 border-top small text-muted">
                                    <i class="fas fa-sticky-note me-1"></i> {{ $log->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                        <h6 class="text-muted">No history found</h6>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function approveAdjustment(id, action) {
        Swal.fire({
            title: action === 'approve' ? 'Approve Request?' : 'Reject Request?',
            text: action === 'approve' ? "This will update the Session's closing amount." : "The request will be marked as rejected.",
            icon: action === 'approve' ? 'warning' : 'info',
            showCancelButton: true,
            confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
            confirmButtonText: 'Confirm'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/register/approve/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ action: action })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
