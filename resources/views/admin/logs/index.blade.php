@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-0 py-md-4">

        {{-- MOBILE HEADER --}}
        <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
            <div class="px-3 py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-history text-secondary me-2"></i>Activity Logs</h6>
                <div>
                    @if(isset($integrityStatus['status']) && $integrityStatus['status'] === 'OK')
                        <div
                            class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-3 shadow-sm">
                            <i class="fas fa-shield-alt"></i> <span class="d-none d-sm-inline ms-1">SECURE</span>
                        </div>
                    @else
                        <button class="btn btn-danger badge px-2 py-1 rounded-3 shadow-sm border-0" data-bs-toggle="modal"
                            data-bs-target="#integrityModal">
                            <i class="fas fa-exclamation-triangle"></i> <span class="d-none d-sm-inline ms-1">TAMPERED</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- DESKTOP HEADER --}}
        <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
            <div>
                <h4 class="fw-bold text-dark mb-1"><i class="fas fa-history text-secondary me-2"></i>Audit Logs</h4>
                <p class="text-muted small mb-0">Track all system activities and security events.</p>
            </div>
            @if(config('safety_flag_features.log_integrity'))
                <div>
                    @if(isset($integrityStatus['status']) && $integrityStatus['status'] === 'OK')
                        <div
                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-4 shadow-sm animate__animated animate__fadeIn">
                            <i class="fas fa-shield-alt me-2"></i>System Integrity: <strong>SECURE</strong>
                        </div>
                    @else
                        <button class="btn btn-danger badge px-3 py-2 rounded-4 shadow-sm animate__animated animate__flash border-0"
                            data-bs-toggle="modal" data-bs-target="#integrityModal">
                            <i class="fas fa-exclamation-triangle me-2"></i>TAMPERING DETECTED (Click for Report)
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <div class="px-3 px-md-0 pt-3 pt-md-0">
            {{-- TOOLBAR --}}
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('logs.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 ps-3"><i
                                        class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-0 py-2"
                                    placeholder="Search logs..." value="{{ request('search') }}">
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
                            <input type="date" name="date" class="form-control bg-light border-0 py-2"
                                value="{{ request('date') }}">
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary shadow-sm w-100 fw-bold py-2">Filter</button>
                                <a href="{{ route('logs.index') }}" class="btn btn-light border w-100 py-2" title="Reset"><i
                                        class="fas fa-undo"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-alt me-2 text-primary"></i>Activity
                        History</h5>
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
                                    @php
                                        $isTampered = isset($integrityStatus['log_id']) && $integrityStatus['log_id'] == $log->id;
                                    @endphp
                                    <tr class="{{ $isTampered ? 'table-danger border-start border-5 border-danger' : '' }}">
                                        <td class="ps-4 text-secondary">
                                            <span
                                                class="fw-bold text-dark">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y') }}</span>
                                            <br>
                                            <span
                                                class="small text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('h:i:s A') }}</span>
                                            @if($isTampered)
                                                <br><span class="badge bg-danger text-white mt-1">TAMPERED</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 border shadow-sm"
                                                    style="width:35px; height:35px;">
                                                    <i class="fas fa-user small text-secondary"></i>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $color = match (strtolower($log->action)) {
                                                    'create', 'add' => 'success',
                                                    'delete', 'remove', 'void' => 'danger',
                                                    'update', 'edit' => 'warning',
                                                    'login', 'logout' => 'info',
                                                    default => 'primary'
                                                };
                                                $icon = match (strtolower($log->action)) {
                                                    'create', 'add' => 'plus-circle',
                                                    'delete', 'remove', 'void' => 'trash-alt',
                                                    'update', 'edit' => 'edit',
                                                    'login' => 'sign-in-alt',
                                                    'logout' => 'sign-out-alt',
                                                    default => 'info-circle'
                                                };
                                            @endphp
                                            <span
                                                class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle rounded-pill px-3">
                                                <i class="fas fa-{{ $icon }} me-1"></i> {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-secondary small">{{ $log->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No activity logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- === MOBILE NATIVE VIEW (TIMELINE) === --}}
                <div class="d-lg-none bg-white p-3">
                    <div class="timeline border-start border-2 border-light ms-2 pb-1">
                        @forelse($logs as $log)
                            @php
                                $isTampered = isset($integrityStatus['log_id']) && $integrityStatus['log_id'] == $log->id;
                            @endphp
                            <div class="position-relative pb-4 ps-4">
                                {{-- Dot --}}
                                @php
                                    $color = match (strtolower($log->action)) {
                                        'create', 'add' => 'success',
                                        'delete', 'remove', 'void' => 'danger',
                                        'update', 'edit' => 'warning',
                                        'login', 'logout' => 'dark',
                                        default => 'primary'
                                    };
                                    if ($isTampered)
                                        $color = 'danger';
                                @endphp
                                <div class="position-absolute bg-white border border-2 border-{{ $color }} rounded-circle shadow-sm"
                                    style="width: 14px; height: 14px; left: -8px; top: 6px;"></div>

                                @if(config('safety_flag_features.log_integrity'))
                                    <div
                                        class="card shadow-sm border-0 {{ $isTampered ? 'bg-danger-subtle border border-danger' : 'bg-light' }} rounded-4">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span
                                                    class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle rounded-pill px-3">
                                                    {{ ucfirst($log->action) }}
                                                </span>
                                                <div class="text-end">
                                                    <small class="text-muted d-block"
                                                        style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, h:i A') }}</small>
                                                    @if($isTampered) <span class="badge bg-danger">TAMPERED</span> @endif
                                                </div>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-1">{{ $log->user->name ?? 'System' }}</h6>
                                            <p class="mb-0 text-secondary small lh-sm">{{ $log->description }}</p>
                                        </div>
                                    </div>
                                @endif
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

    {{-- SECURITY REPORT MODAL --}}
    @if(isset($integrityStatus['status']) && $integrityStatus['status'] !== 'OK')
        <div class="modal fade" id="integrityModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-shield-virus me-2"></i>Security Alert</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 60px; height: 60px;">
                                <i class="fas fa-user-secret fa-2x"></i>
                            </div>
                            <h5 class="fw-bold text-danger">Tampering Detected!</h5>
                            <p class="text-muted small">The system security chain has been broken. This indicates unauthorized
                                modification of the database.</p>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                <span class="text-secondary small fw-bold">Compromised Log ID:</span>
                                <span class="fw-bold font-monospace text-dark">#{{ $integrityStatus['log_id'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary small fw-bold">Affected User:</span>
                                <span class="text-dark">{{ $integrityStatus['user'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary small fw-bold">Date:</span>
                                <span class="text-dark">{{ $integrityStatus['date'] ?? 'N/A' }}</span>
                            </div>
                            <div class="mb-2 mt-3">
                                <span class="text-secondary small fw-bold d-block mb-1">Reason:</span>
                                <span
                                    class="text-danger small bg-danger bg-opacity-10 px-2 py-1 rounded">{{ $integrityStatus['reason'] }}</span>
                            </div>
                        </div>

                        <details class="bg-white border rounded-3 shadow-sm">
                            <summary class="px-3 py-2 fw-bold text-dark d-flex align-items-center justify-content-between"
                                style="cursor: pointer;">
                                <span><i class="fas fa-code me-2 text-secondary"></i>View Technical Details</span>
                                <i class="fas fa-chevron-down small text-muted"></i>
                            </summary>
                            <div class="p-3 border-top bg-light">
                                <label class="small text-muted fw-bold d-block mb-1">Expected Hash (valid):</label>
                                <div
                                    class="bg-dark text-success p-2 rounded small font-monospace text-break mb-3 border border-success">
                                    {{ $integrityStatus['expected_hash'] ?? 'N/A' }}
                                </div>

                                <label class="small text-muted fw-bold d-block mb-1">Actual Hash (found in DB):</label>
                                <div
                                    class="bg-dark text-danger p-2 rounded small font-monospace text-break border border-danger">
                                    {{ $integrityStatus['actual_hash'] ?? 'N/A' }}
                                </div>
                            </div>
                        </details>

                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection