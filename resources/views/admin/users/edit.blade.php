@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold text-dark m-0 tracking-tight">Edit User</h3>
                    <p class="text-muted small m-0">Update account details and permissions.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2"></i>Update Account: {{ $user->name }}</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @php
                            $currentUserRole = strtolower(auth()->user()->role);
                            $isSelfNonAdmin = (auth()->id() == $user->id && $currentUserRole != 'admin');
                        @endphp

                        <ul class="nav nav-pills mb-4 gap-2 p-1 bg-light rounded-pill" id="pills-tab" role="tablist" style="width: fit-content;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill active fw-bold small" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-selected="true">Account Details</button>
                            </li>
                            @if(!$isSelfNonAdmin)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-bold small" id="pills-perms-tab" data-bs-toggle="pill" data-bs-target="#pills-perms" type="button" role="tab" aria-selected="false">Permissions <span class="badge bg-warning text-dark ms-1">PRO</span></button>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content" id="pills-tabContent">
                            {{-- TAB 1: ACCOUNT --}}
                            <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab">
                                <div class="row g-4">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-secondary">Full Name</label>
                                        <input type="text" name="name" class="form-control bg-light border-0" value="{{ old('name', $user->name) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small text-uppercase text-secondary">Email Address</label>
                                        <input type="email" name="email" class="form-control bg-light border-0" value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-uppercase text-secondary">Role</label>
                                        <select name="role" class="form-select bg-light border-0" {{ $isSelfNonAdmin ? 'disabled' : '' }}>
                                            @if($currentUserRole === 'admin')
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                                            @endif
                                            <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager (Store Operations)</option>
                                            <option value="supervisor" {{ $user->role == 'supervisor' ? 'selected' : '' }}>Supervisor (Overrides & Refunds)</option>
                                            <option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier (POS Only)</option>
                                            <option value="stock_clerk" {{ $user->role == 'stock_clerk' ? 'selected' : '' }}>Stock Clerk (Inventory Only)</option>
                                            <option value="auditor" {{ $user->role == 'auditor' ? 'selected' : '' }}>Auditor (Read Only)</option>
                                        </select>
                                        @if($isSelfNonAdmin)
                                            <input type="hidden" name="role" value="{{ $user->role }}">
                                            <div class="form-text text-muted"><i class="fas fa-lock me-1"></i> You cannot change your own role.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="alert alert-light border-0 shadow-sm rounded-4 mt-5 p-4 bg-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <h6 class="text-danger fw-bold m-0">Security Update</h6>
                                    </div>
                                    <p class="small text-muted mb-3">Leave the field below blank if you do NOT want to change the password.</p>
                                    
                                    <label class="form-label fw-bold small text-uppercase text-secondary">New Password</label>
                                    <input type="password" name="password" class="form-control bg-white border shadow-sm" placeholder="Enter new password to reset...">
                                </div>
                            </div>

                            {{-- TAB 2: PERMISSIONS --}}
                            @if(!$isSelfNonAdmin)
                            <div class="tab-pane fade" id="pills-perms" role="tabpanel" aria-labelledby="pills-perms-tab">
                                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
                                    <div class="d-flex">
                                        <i class="fas fa-info-circle fs-4 me-3 mt-1"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Advanced Overrides</h6>
                                            <p class="small mb-0 opacity-75">
                                                By default, permissions are inherited from the <strong>Role</strong>. 
                                                You can explicitly <strong>Allow</strong> or <strong>Deny</strong> specific actions here to override the role.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- DESKTOP VIEW --}}
                                <div class="table-responsive d-none d-lg-block">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="small text-uppercase text-secondary py-3 ps-3">Permission</th>
                                                <th class="text-center small text-uppercase text-secondary py-3" style="width: 100px;">Inherit</th>
                                                <th class="text-center small text-uppercase text-secondary py-3" style="width: 100px;">Allow</th>
                                                <th class="text-center small text-uppercase text-secondary py-3" style="width: 100px;">Deny</th>
                                                <th class="text-center small text-uppercase text-secondary py-3" style="width: 120px;">Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(\App\Enums\Permission::cases() as $perm)
                                            <tr>
                                                <td class="ps-3 py-3">
                                                    <div class="fw-bold text-dark">{{ ucwords(str_replace('.', ' ', $perm->value)) }}</div>
                                                    <div class="small text-muted">{{ $perm->label() }}</div>
                                                </td>
                                                
                                                @php
                                                    $currentOverride = null;
                                                    if ($user->permissions && array_key_exists($perm->value, $user->permissions)) {
                                                        $currentOverride = (int) $user->permissions[$perm->value]; 
                                                    }
                                                    
                                                    // Check Default Status
                                                    $isDefaultAllowed = in_array($perm->value, $rolePermissions ?? []);
                                                @endphp

                                                <td class="text-center align-middle">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="form-check d-flex justify-content-center mb-1">
                                                            <input class="form-check-input permission-radio" type="radio" name="permissions[{{ $perm->value }}]" value="" {{ is_null($currentOverride) ? 'checked' : '' }} data-perm="{{ $perm->value }}" data-default="{{ $isDefaultAllowed ? '1' : '0' }}" data-badge-id="status_badge_{{ \Illuminate\Support\Str::slug($perm->value) }}">
                                                        </div>
                                                        <span class="badge rounded-pill {{ $isDefaultAllowed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border {{ $isDefaultAllowed ? 'border-success' : 'border-danger' }} small" style="font-size: 0.65rem;">
                                                            {{ $isDefaultAllowed ? 'DEFAULT: YES' : 'DEFAULT: NO' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input bg-success border-success permission-radio" type="radio" name="permissions[{{ $perm->value }}]" value="1" {{ $currentOverride === 1 ? 'checked' : '' }} data-perm="{{ $perm->value }}" data-badge-id="status_badge_{{ \Illuminate\Support\Str::slug($perm->value) }}">
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input bg-danger border-danger permission-radio" type="radio" name="permissions[{{ $perm->value }}]" value="0" {{ $currentOverride === 0 ? 'checked' : '' }} data-perm="{{ $perm->value }}" data-badge-id="status_badge_{{ \Illuminate\Support\Str::slug($perm->value) }}">
                                                    </div>
                                                </td>
                                                
                                                @php
                                                    // Determine Initial Badge State
                                                    $badgeClass = '';
                                                    $badgeIcon = '';
                                                    $badgeText = '';
                                                    
                                                    if ($currentOverride === 1) {
                                                        $badgeClass = 'bg-success text-white border-success';
                                                        $badgeIcon = 'fa-check-circle';
                                                        $badgeText = 'ALLOWED (Explicit)';
                                                    } elseif ($currentOverride === 0) {
                                                        $badgeClass = 'bg-danger text-white border-danger';
                                                        $badgeIcon = 'fa-ban';
                                                        $badgeText = 'DENIED (Explicit)';
                                                    } else {
                                                        // Inherit
                                                        if ($isDefaultAllowed) {
                                                            $badgeClass = 'bg-success text-white border-success';
                                                            $badgeIcon = 'fa-check-circle';
                                                            $badgeText = 'ALLOWED (Default)';
                                                        } else {
                                                            $badgeClass = 'bg-danger text-white border-danger';
                                                            $badgeIcon = 'fa-ban';
                                                            $badgeText = 'DENIED (Default)';
                                                        }
                                                    }
                                                @endphp

                                                <td class="text-center align-middle">
                                                    <span id="status_badge_{{ \Illuminate\Support\Str::slug($perm->value) }}" class="badge rounded-pill fw-bold px-3 py-2 border shadow-sm {{ $badgeClass }}" style="font-size: 0.75rem;">
                                                        <i class="fas {{ $badgeIcon }} me-1"></i> {{ $badgeText }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- MOBILE VIEW (Cards + Segmented Control) --}}
                                <div class="d-lg-none">
                                    @foreach(\App\Enums\Permission::cases() as $perm)
                                    @php
                                        $currentOverride = null;
                                        if ($user->permissions && array_key_exists($perm->value, $user->permissions)) {
                                            $currentOverride = (int) $user->permissions[$perm->value];
                                        }
                                        $uId = 'perm_mobile_' . Str::slug($perm->value);
                                        $isDefaultAllowed = in_array($perm->value, $rolePermissions ?? []);
                                    @endphp
                                    <div class="card bg-light border-0 shadow-sm rounded-4 mb-3">
                                        <div class="card-body p-3">
                                            <div class="mb-3">
                                                <div class="fw-bold text-dark h6 mb-1">{{ ucwords(str_replace('.', ' ', $perm->value)) }}</div>
                                                <div class="small text-secondary">{{ $perm->label() }}</div>
                                            </div>
                                            
                                            <div class="btn-group w-100 shadow-sm" role="group">
                                                <input type="radio" class="btn-check" name="permissions[{{ $perm->value }}]" id="{{ $uId }}_inherit" value="" {{ is_null($currentOverride) ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary btn-sm py-2" for="{{ $uId }}_inherit">
                                                    Default ({{ $isDefaultAllowed ? 'Allow' : 'Deny' }})
                                                </label>
                                            
                                                <input type="radio" class="btn-check" name="permissions[{{ $perm->value }}]" id="{{ $uId }}_allow" value="1" {{ $currentOverride === 1 ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success btn-sm py-2" for="{{ $uId }}_allow">Allow</label>
                                            
                                                <input type="radio" class="btn-check" name="permissions[{{ $perm->value }}]" id="{{ $uId }}_deny" value="0" {{ $currentOverride === 0 ? 'checked' : '' }}>
                                                <label class="btn btn-outline-danger btn-sm py-2" for="{{ $uId }}_deny">Deny</label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('users.index') }}" class="btn btn-light text-secondary rounded-pill px-4 fw-bold shadow-sm">Cancel</a>
                            <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-lg">
                                <i class="fas fa-check-circle me-2"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADMIN APPROVAL MODAL (Always Rendered, Hidden by Default) --}}
<div class="modal fade" id="adminApprovalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Request Role Approval</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                
                {{-- STEP 1: SELECT ADMIN --}}
                <div id="stepSelectAdmin">
                    <p class="text-secondary small mb-4">Changing a user's role requires approval. Select an administrator to notify:</p>
                    
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Select Administrator</label>
                        <select id="adminSelect" class="form-select form-select-lg bg-light border-0">
                            @foreach($admins ?? [] as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="button" id="btnSendRequest" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="fas fa-bell me-2"></i> Send Notification
                        </button>
                    </div>
                </div>

                {{-- STEP 2: WAITING --}}
                <div id="stepWaiting" class="d-none">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="fw-bold animate__animated animate__pulse animate__infinite">Waiting for Approval...</h5>
                    <p class="text-muted small">Notification sent to Admin. Please wait for their response.</p>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-secondary d-block">Expires in <span id="timerCountdown">10:00</span></small>
                </div>

                {{-- STEP 3: RESULT --}}
                <div id="stepResult" class="d-none">
                    <div id="resultIcon" class="mb-3 display-4"></div>
                    <h5 id="resultTitle" class="fw-bold mb-2"></h5>
                    <p id="resultMessage" class="text-muted small"></p>
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. PERMISSION BADGE LOGIC (Existing) ---
    function updateBadge(badgeId, value, defaultValue) {
        const badge = document.getElementById(badgeId);
        if(!badge) return;

        let isAllowed = false;
        let label = '';

        if (value === '1') {
            isAllowed = true;
            label = 'ALLOWED (Explicit)';
        } else if (value === '0') {
            isAllowed = false;
            label = 'DENIED (Explicit)';
        } else {
            // Inherit
            isAllowed = (defaultValue === '1');
            label = isAllowed ? 'ALLOWED (Default)' : 'DENIED (Default)';
        }

        if (isAllowed) {
            badge.className = 'badge rounded-pill fw-bold px-3 py-2 border shadow-sm bg-success text-white border-success';
            badge.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + label;
        } else {
            badge.className = 'badge rounded-pill fw-bold px-3 py-2 border shadow-sm bg-danger text-white border-danger';
            badge.innerHTML = '<i class="fas fa-ban me-1"></i> ' + label;
        }
    }

    const radios = document.querySelectorAll('.permission-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const groupName = this.name; 
            const defaultRadio = document.querySelector(`input[name="${groupName}"][value=""]`);
            const defaultValue = defaultRadio ? defaultRadio.dataset.default : '0';
            if(this.dataset.badgeId) {
                updateBadge(this.dataset.badgeId, this.value, defaultValue);
            }
        });
    });

    const uniquePerms = new Set();
    radios.forEach(r => uniquePerms.add(r.name));
    uniquePerms.forEach(name => {
        const checked = document.querySelector(`input[name="${name}"]:checked`);
        if(checked) {
             const defaultRadio = document.querySelector(`input[name="${name}"][value=""]`);
             const defaultValue = defaultRadio ? defaultRadio.dataset.default : '0';
             if(checked.dataset.badgeId) {
                updateBadge(checked.dataset.badgeId, checked.value, defaultValue);
             }
        }
    });

    // --- 2. ADMIN APPROVAL LOGIC (Async Notification) ---
    const form = document.getElementById('editUserForm');
    const roleSelect = document.querySelector('select[name="role"]');
    
    // PHP to JS Variable Injection
    const isManager = {{ strtolower(auth()->user()->role) === 'manager' ? 'true' : 'false' }};
    const currentRole = "{{ $user->role }}";
    const targetUserId = "{{ $user->id }}";
    
    let approvalToken = null;
    let pollInterval = null;

    if (form && isManager) {
        console.log('Manager Approval Script Active');
        
        form.addEventListener('submit', function(e) {
            const newRole = roleSelect ? roleSelect.value : currentRole;
            
            // Check if role changed and approval is missing
            if (newRole !== currentRole && !approvalToken) {
                e.preventDefault(); 
                e.stopPropagation();
                
                console.log('Role change intercepted. Showing modal.');

                // Show Modal
                const modalEl = document.getElementById('adminApprovalModal');
                if (!modalEl) {
                    alert('Error: Approval modal element missing.');
                    return;
                }

                if (typeof bootstrap === 'undefined') {
                    // Fallback if bootstrap is not loaded
                    alert('Action Intercepted: Admin approval required.');
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    document.body.classList.add('modal-open');
                    
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'tempBackdrop';
                    document.body.appendChild(backdrop);
                } else {
                     const modal = new bootstrap.Modal(modalEl);
                     modal.show();
                }

                // Elements
                const stepSelect = document.getElementById('stepSelectAdmin');
                const stepWait = document.getElementById('stepWaiting');
                const stepResult = document.getElementById('stepResult');
                
                // Reset State
                stepSelect.classList.remove('d-none');
                stepWait.classList.add('d-none');
                stepResult.classList.add('d-none');

                // SEND REQUEST
                const btnSend = document.getElementById('btnSendRequest');
                btnSend.onclick = function() {
                    const btn = this;
                    const adminSelect = document.getElementById('adminSelect');
                    const adminId = adminSelect ? adminSelect.value : null;
                    
                    if(!adminId) {
                        alert('No administrator selected.');
                        return;
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

                    fetch('{{ route("approval.send") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ 
                            approver_id: adminId, 
                            target_user_id: targetUserId,
                            new_role: newRole
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            stepSelect.classList.add('d-none');
                            stepWait.classList.remove('d-none');
                            pollStatus(data.request_id);
                        } else {
                            alert(data.message || 'Failed to send request.');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-bell me-2"></i> Send Notification';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Network connection error.');
                        btn.disabled = false;
                    });
                };

                function pollStatus(requestId) {
                    let timeLeft = 600; 
                    if (pollInterval) clearInterval(pollInterval);
                    
                    pollInterval = setInterval(() => {
                        fetch(`/admin/approval/${requestId}/status`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'approved') {
                                clearInterval(pollInterval);
                                
                                // Dynamic Message Logic
                                const roleHierarchy = {
                                    'admin': 50,
                                    'manager': 40,
                                    'supervisor': 30,
                                    'cashier': 20,
                                    'stock_clerk': 10,
                                    'auditor': 10
                                };
                                
                                const oldVal = roleHierarchy[currentRole.toLowerCase()] || 0;
                                const newVal = roleHierarchy[newRole.toLowerCase()] || 0;
                                const actionType = newVal > oldVal ? 'promoted' : 'demoted'; // or just assigned
                                const targetName = "{{ $user->name }}";
                                
                                // Construct precise message
                                let messageText = `Your request has been approved. System has automatically assigned ${targetName} to the ${newRole.toUpperCase()} role.`;
                                if (newVal > oldVal) {
                                    messageText = `Your request has been approved. ${targetName} has been promoted to ${newRole.toUpperCase()}.`;
                                } else if (newVal < oldVal) {
                                    messageText = `Your request has been approved. ${targetName} has been demoted to ${newRole.toUpperCase()}.`;
                                }

                                Swal.fire({
                                    title: 'Success!',
                                    text: messageText,
                                    icon: 'success',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    willClose: () => {
                                        Swal.fire({
                                            title: 'Updating...',
                                            text: 'Please wait while we apply changes.',
                                            allowOutsideClick: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        // Set Token & Submit
                                        approvalToken = data.token;
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'admin_approval_token';
                                        input.value = approvalToken;
                                        form.appendChild(input);
                                        
                                        form.submit();
                                    }
                                });
                            } else if (data.status === 'rejected') {
                                clearInterval(pollInterval);
                                Swal.fire({
                                    title: 'Rejected',
                                    text: 'Admin has denied your request.',
                                    icon: 'error',
                                    timer: 1000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    willClose: () => {
                                        window.location.reload();
                                    }
                                });
                            } else if (data.status === 'expired') {
                                clearInterval(pollInterval);
                                showResult('warning', 'Expired', 'Request timed out.');
                            }
                        });
                    }, 3000); 
                }

                function showResult(type, title, msg) {
                    stepWait.classList.add('d-none');
                    stepResult.classList.remove('d-none');
                    document.getElementById('resultTitle').className = `fw-bold mb-2 text-${type}`;
                    document.getElementById('resultTitle').textContent = title;
                    document.getElementById('resultMessage').textContent = msg;
                    
                    const icon = type === 'success' ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
                    document.getElementById('resultIcon').innerHTML = icon;
                }
            } else {
                console.log('No role change or already approved.');
            }
        });
    }
});
</script>
@endsection