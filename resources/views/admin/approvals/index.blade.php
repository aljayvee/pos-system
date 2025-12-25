@extends('admin.layout')

@section('title', 'Cash Register Approvals')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-check text-primary me-2"></i>Pending Register Adjustments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Requester</th>
                                <th>Session Info</th>
                                <th>Original Amount</th>
                                <th>New Amount</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adj)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                            {{ substr($adj->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $adj->user->name }}</div>
                                            <small class="text-muted">{{ $adj->user->role }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Session #{{ $adj->cash_register_session_id }}</span>
                                </td>
                                <td class="text-danger fw-bold">₱{{ number_format($adj->original_amount, 2) }}</td>
                                <td class="text-success fw-bold">₱{{ number_format($adj->new_amount, 2) }}</td>
                                <td>
                                    <span class="text-dark">{{ $adj->reason }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $adj->created_at->format('M d, h:i A') }}
                                </td>
                                <td class="text-end pe-4">
                                    <button onclick="approveAdjustment({{ $adj->id }}, 'approve')" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                    <button onclick="approveAdjustment({{ $adj->id }}, 'reject')" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="opacity-50">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                        <p class="mb-0 fw-bold">All Caught Up!</p>
                                        <small>No pending adjustments found.</small>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
