@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history text-secondary me-2"></i>Activity Logs</h6>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fas fa-history text-secondary me-2"></i>Audit Logs</h4>
            <p class="text-muted small mb-0">Track all system activities and security events.</p>
        </div>
        <div>
            @if($integrityStatus === true)
                <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-4 shadow-sm animate__animated animate__fadeIn">
                    <i class="fas fa-shield-alt me-2"></i>System Integrity: <strong>SECURE</strong>
                </div>
            @else
                <div class="badge bg-danger text-white px-3 py-2 rounded-4 shadow-sm animate__animated animate__flash">
                    <i class="fas fa-exclamation-triangle me-2"></i>TAMPERING DETECTED (Log #{{ $integrityStatus }})
                </div>
            @endif
        </div>
    </div>

    <div class="px-3 px-md-0 pt-3 pt-md-0">
        {{-- TOOLBAR --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('logs.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0 py-2" placeholder="Search logs..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="action" class="form-select bg-light border-0 py-2">
                            <option value="">All Actions</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                            <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                            <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                            <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <input type="date" name="date" class="form-control bg-light border-0 py-2" value="{{ request('date') }}">
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary shadow-sm w-100 fw-bold py-2">Filter</button>
                            <a href="{{ route('logs.index') }}" class="btn btn-light border w-100 py-2" title="Reset"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-alt me-2 text-primary"></i>Activity History</h5>
            </div>
            
            {{-- DESKTOP TABLE --}}
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold text-secondary">
                            <tr>
                                <th class="ps-4 py-3" style="width: 20%;">Date & Time</th>
                                <th class="py-3" style="width: 20%;">User</th>
                                <th class="py-3" style="width: 15%;">Action</th>
                                <th class="py-3 pe-4">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 text-secondary">
                                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y') }}</span> <br>
                                    <span class="small text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('h:i:s A') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 border shadow-sm" style="width:35px; height:35px;">
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
                                            'update', 'edit' => 'warning',
                                            'login', 'logout' => 'info',
                                            default => 'primary'
                                        };
                                        $icon = match(strtolower($log->action)) {
                                            'create', 'add' => 'plus-circle',
                                            'delete', 'remove', 'void' => 'trash-alt',
                                            'update', 'edit' => 'edit',
                                            'login' => 'sign-in-alt',
                                            'logout' => 'sign-out-alt',
                                            default => 'info-circle'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle rounded-pill px-3">
                                        <i class="fas fa-{{ $icon }} me-1"></i> {{ ucfirst($log->action) }}
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
                <div class="timeline border-start border-2 border-light ms-2 pb-1">
                    @forelse($logs as $log)
                    <div class="position-relative pb-4 ps-4">
                        {{-- Dot --}}
                        @php
                            $color = match(strtolower($log->action)) {
                                'create', 'add' => 'success',
                                'delete', 'remove', 'void' => 'danger',
                                'update', 'edit' => 'warning',
                                'login', 'logout' => 'dark',
                                default => 'primary'
                            };
                        @endphp
                        <div class="position-absolute bg-white border border-2 border-{{ $color }} rounded-circle shadow-sm" style="width: 14px; height: 14px; left: -8px; top: 6px;"></div>
                        
                        <div class="card shadow-sm border-0 bg-light rounded-4">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle rounded-pill px-3">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, h:i A') }}</small>
                                </div>
                                
                                <h6 class="fw-bold text-dark mb-1">{{ $log->user->name ?? 'System' }}</h6>
                                <p class="mb-0 text-secondary small lh-sm">{{ $log->description }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">No activity logs found.</div>
                    @endforelse
                </div>
            </div>

            @if($logs->hasPages())
            <div class="card-footer bg-white border-top-0 d-flex justify-content-center py-4">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection