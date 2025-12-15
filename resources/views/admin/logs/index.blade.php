@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <div>
            <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-clipboard-list text-secondary me-2"></i>Audit Logs</h1>
            <p class="text-muted small mb-0">Track system activities and user actions.</p>
        </div>
        
        {{-- Optional: Filter button could go here in future updates --}}
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
                            <td class="pe-4 text-secondary small">
                                {{ $log->description }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-2x mb-3 opacity-25"></i>
                                <p class="mb-0">No activity logs found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE TIMELINE VIEW --}}
        <div class="d-lg-none">
            <div class="list-group list-group-flush">
                @forelse($logs as $log)
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:35px; height:35px">
                                <i class="fas fa-user-circle text-secondary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at->format('M d, h:i A') }}</small>
                            </div>
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
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($log->action) }}</span>
                    </div>
                    
                    <div class="mt-2 ps-1 border-start border-3 ms-3 ps-3">
                        <p class="mb-0 small text-secondary">{{ $log->description }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">No activity logs found.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- PAGINATION --}}
        @if($logs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection