@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <div>
            <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-clipboard-list text-secondary me-2"></i>Audit Logs</h1>
            <p class="text-muted small mb-0">Track system activities.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        
        {{-- DESKTOP TABLE --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-4 py-3" style="width: 20%;">Date & Time</th>
                            <th class="py-3" style="width: 15%;">User</th>
                            <th class="py-3" style="width: 15%;">Action</th>
                            <th class="py-3 pe-4">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 text-muted">
                                {{ $log->created_at->format('M d, Y') }} <br>
                                <small>{{ $log->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px;">
                                        <i class="fas fa-user small text-secondary"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $color = match(strtolower($log->action)) {
                                        'create', 'add' => 'success',
                                        'delete', 'remove', 'void' => 'danger',
                                        'update', 'edit' => 'warning text-dark',
                                        'login', 'logout' => 'dark',
                                        default => 'primary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}-subtle text-{{ str_replace('text-dark', 'dark', $color) }} border border-{{ str_replace('text-dark', 'dark', $color) }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="pe-4 text-secondary small">{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted">No activity logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- === MOBILE NATIVE VIEW (TIMELINE) === --}}
        <div class="d-lg-none bg-white p-3">
            <div class="timeline">
                @forelse($logs as $log)
                <div class="position-relative pb-4 ps-4 border-start border-2 border-light">
                    {{-- Dot --}}
                    <div class="position-absolute bg-white border border-2 border-secondary rounded-circle" style="width: 12px; height: 12px; left: -7px; top: 5px;"></div>
                    
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $log->user->name ?? 'System' }}</span>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at->format('M d, h:i A') }}</small>
                    </div>

                    @php
                        $badgeClass = match(strtolower($log->action)) {
                            'create', 'add' => 'bg-success-subtle text-success',
                            'delete', 'remove', 'void' => 'bg-danger-subtle text-danger',
                            'update', 'edit' => 'bg-warning-subtle text-warning text-dark-emphasis',
                            'login', 'logout' => 'bg-dark-subtle text-dark',
                            default => 'bg-primary-subtle text-primary'
                        };
                    @endphp
                    
                    <div class="mb-1">
                        <span class="badge {{ $badgeClass }} rounded-pill" style="font-size: 0.65rem;">{{ ucfirst($log->action) }}</span>
                    </div>
                    
                    <p class="mb-0 text-muted small" style="line-height: 1.4;">{{ $log->description }}</p>
                </div>
                @empty
                <div class="text-center py-5 text-muted">No activity logs found.</div>
                @endforelse
            </div>
        </div>

        @if($logs->hasPages())
        <div class="card-footer bg-white py-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection