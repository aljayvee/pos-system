@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-0 py-md-4 bg-light min-vh-100">
        <div class="row justify-content-center m-0">
            <div class="col-lg-8 p-0">

                {{-- MOBILE HEADER --}}
                <div class="d-flex d-lg-none align-items-center justify-content-between p-3 bg-white shadow-sm sticky-top"
                    style="z-index: 1020;">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('users.index') }}" class="btn btn-light rounded-circle shadow-sm"
                            style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                            <i class="fas fa-arrow-left text-dark"></i>
                        </a>
                        <h5 class="mb-0 fw-bold">New User</h5>
                    </div>
                </div>

                {{-- DESKTOP HEADER --}}
                <div class="d-none d-lg-flex align-items-center justify-content-between mb-4 mt-3">
                    <div>
                        <h3 class="fw-bold text-dark m-0 tracking-tight">Create Account</h3>
                        <p class="text-muted small m-0">Add a new user to the system.</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>

                <form id="createUserForm" action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div id="stepForm" class="pb-5 mb-5">

                        {{-- SECTION 1: IDENTITY --}}
                        <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="p-3 bg-light border-bottom d-lg-none">
                                    <h6 class="text-uppercase fw-bold text-muted small mb-0 spacing-1">Identity</h6>
                                </div>
                                <div class="p-3 p-md-4 bg-white">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Full Name</label>
                                        <input type="text" name="name"
                                            class="form-control form-control-lg bg-light border-0"
                                            placeholder="e.g. Juan Cashier" required style="font-size: 1rem;">
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold small text-secondary">Email Address</label>
                                        <input type="email" name="email"
                                            class="form-control form-control-lg bg-light border-0"
                                            placeholder="user@pos.com" required style="font-size: 1rem;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: ROLE ACCESS --}}
                        <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="p-3 bg-light border-bottom d-lg-none">
                                    <h6 class="text-uppercase fw-bold text-muted small mb-0 spacing-1">Access Control</h6>
                                </div>
                                <div class="p-3 p-md-4 bg-white">
                                    <div>
                                        <label class="form-label fw-bold small text-secondary">Role</label>
                                        <select name="role" class="form-select form-select-lg bg-light border-0"
                                            style="font-size: 1rem;">
                                            @if(auth()->user()->role === 'admin')
                                                <option value="admin">Admin (Full System Access)</option>
                                            @endif
                                            <option value="manager">Manager (Operations & Overrides)</option>
                                            <option value="supervisor">Supervisor (Sales Management)</option>
                                            <option value="cashier">Cashier (POS & Sales)</option>
                                            <option value="stock_clerk">Stock Clerk (Inventory Only)</option>
                                            <option value="auditor">Auditor (Read-Only Access)</option>
                                        </select>
                                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1 text-primary"></i>
                                            Select the primary role.</div>
                                    </div>

                                    @if(auth()->user()->role === 'admin')
                                        <div class="mt-3">
                                            <label class="form-label fw-bold small text-secondary">Assigned Branch</label>
                                            <select name="store_id" class="form-select form-select-lg bg-light border-0"
                                                style="font-size: 1rem;">
                                                @foreach($stores as $store)
                                                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                        {{ $store->name }} {{ $store->id == 1 ? '(HQ)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text mt-2">User will be locked to this store context.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 3: SECURITY --}}
                        <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-3 overflow-hidden">
                            <div class="card-body p-0">
                                <div class="p-3 bg-light border-bottom d-lg-none">
                                    <h6 class="text-uppercase fw-bold text-muted small mb-0 spacing-1">Security</h6>
                                </div>
                                <div class="p-3 p-md-4 bg-white">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Password</label>
                                        <input type="password" name="password"
                                            class="form-control form-control-lg bg-light border-0" required minlength="6"
                                            placeholder="••••••••">
                                    </div>
                                    <div>
                                        <label class="form-label fw-bold small text-secondary">Confirm Password</label>
                                        <input type="password" name="password_confirmation"
                                            class="form-control form-control-lg bg-light border-0" required
                                            placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger shadow-sm border-0 rounded-4 mx-3 mx-lg-0 mb-4">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- DESKTOP ACTION --}}
                        <div class="d-none d-lg-flex justify-content-end mt-4">
                            <button type="submit" id="btnCreateDesktop"
                                class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg">
                                <i class="fas fa-save me-2"></i> Create Account
                            </button>
                        </div>
                    </div>

                    {{-- MOBILE STATIC ACTION BAR --}}
                    <div class="d-lg-none mt-4 pb-5">
                        <button type="submit" id="btnCreateMobile"
                            class="btn btn-primary w-100 rounded-pill fw-bold py-3 shadow-lg" style="font-size: 1.1rem;">
                            Create Account
                        </button>
                    </div>

                    <!-- WAITING SCREEN (Hidden by default) -->
                    <div id="stepWaiting" class="d-none text-center py-5">
                        <div class="spinner-border text-primary my-4" style="width: 3rem; height: 3rem;" role="status">
                        </div>
                        <h4 class="fw-bold text-dark">Waiting for Approval...</h4>
                        <p class="text-muted mb-4">An Admin must approve this new account creation.</p>
                        <div class="alert alert-warning d-inline-block px-4 border-0 rounded-pill shadow-sm">
                            <i class="fas fa-clock me-2"></i> Please check with an Administrator.
                        </div>
                    </div>
                </form>

                <!-- ADMIN SELECTION MODAL (Existing logic preserved) -->
                <div class="modal fade" id="adminSelectModal" tabindex="-1" data-bs-backdrop="static"
                    data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header bg-primary text-white border-0">
                                <h5 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Approval Required</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <div class="mb-4">
                                    <div
                                        class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                                        <i class="fas fa-user-lock fa-2x"></i>
                                    </div>
                                    <p class="text-muted small">As a Manager, you need an Administrator's approval to create
                                        a new account.</p>
                                </div>

                                <div class="form-floating mb-4 text-start">
                                    <select class="form-select border-0 bg-light fw-bold" id="approverId">
                                        <option value="" selected disabled>Select an Admin...</option>
                                        @if(isset($admins))
                                            @foreach($admins as $admin)
                                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <label for="approverId">Select Approver</label>
                                </div>

                                <button type="button" id="btnConfirmRequest"
                                    class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> Send Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const form = document.getElementById('createUserForm');
                        const isManager = {{ strtolower(auth()->user()->role) === 'manager' ? 'true' : 'false' }};

                        let approvalModal = null;
                        if (typeof bootstrap !== 'undefined') {
                            const modalEl = document.getElementById('adminSelectModal');
                            if (modalEl) approvalModal = new bootstrap.Modal(modalEl);
                        }

                        if (form) {
                            form.addEventListener('submit', function (e) {
                                if (isManager) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (approvalModal) {
                                        approvalModal.show();
                                    } else {
                                        const modalEl = document.getElementById('adminSelectModal');
                                        if (modalEl) {
                                            modalEl.classList.add('show');
                                            modalEl.style.display = 'block';
                                            document.body.classList.add('modal-open');
                                            const backdrop = document.createElement('div');
                                            backdrop.className = 'modal-backdrop fade show';
                                            document.body.appendChild(backdrop);
                                        }
                                    }
                                } else {
                                    e.preventDefault();
                                    submitForm(form);
                                }
                            });
                        }

                        const btnConfirm = document.getElementById('btnConfirmRequest');
                        if (btnConfirm) {
                            btnConfirm.addEventListener('click', function () {
                                const approverId = document.getElementById('approverId').value;
                                if (!approverId) {
                                    Swal.fire('Error', 'Please select an administrator.', 'error');
                                    return;
                                }

                                if (approvalModal) approvalModal.hide();
                                else {
                                    const modalEl = document.getElementById('adminSelectModal');
                                    modalEl.classList.remove('show');
                                    modalEl.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                    const bd = document.querySelector('.modal-backdrop');
                                    if (bd) bd.remove();
                                }

                                submitForm(form, approverId);
                            });
                        }
                    });

                    function submitForm(form, approverId = null) {
                        const stepForm = document.getElementById('stepForm');
                        const stepWaiting = document.getElementById('stepWaiting');

                        // Disable both desktop and mobile buttons
                        const btnDesktop = document.getElementById('btnCreateDesktop');
                        const btnMobile = document.getElementById('btnCreateMobile');

                        if (btnDesktop) {
                            btnDesktop.disabled = true;
                            btnDesktop.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                        }
                        if (btnMobile) {
                            btnMobile.disabled = true;
                            btnMobile.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                        }

                        const formData = new FormData(form);
                        if (approverId) {
                            formData.append('approver_id', approverId);
                        }

                        const data = Object.fromEntries(formData.entries());

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        })
                            .then(res => res.json())
                            .then(resp => {
                                if (resp.pending_approval) {
                                    stepForm.classList.add('d-none');
                                    // Hide mobile bar too
                                    if (btnMobile) btnMobile.parentElement.classList.add('d-none');

                                    stepWaiting.classList.remove('d-none');
                                    startPolling(resp.request_id);
                                } else if (resp.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'User created successfully.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = "{{ route('users.index') }}";
                                    });
                                } else {
                                    throw new Error(resp.message || 'Failed to create user.');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', err.message || 'An error occurred.', 'error');

                                if (btnDesktop) {
                                    btnDesktop.disabled = false;
                                    btnDesktop.innerHTML = '<i class="fas fa-save me-2"></i> Create Account';
                                }
                                if (btnMobile) {
                                    btnMobile.disabled = false;
                                    btnMobile.innerHTML = 'Create Account';
                                }
                            });
                    }

                    function startPolling(requestId) {
                        const pollInterval = setInterval(() => {
                            fetch(`/admin/approval/${requestId}/status`)
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'approved') {
                                        clearInterval(pollInterval);
                                        Swal.fire({
                                            title: 'Approved!',
                                            text: 'Account has been activated.',
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            window.location.href = "{{ route('users.index') }}";
                                        });
                                    } else if (data.status === 'rejected') {
                                        clearInterval(pollInterval);
                                        Swal.fire({
                                            title: 'Rejected',
                                            text: 'Request denied.',
                                            icon: 'error'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }
                                })
                                .catch(e => console.error(e));
                        }, 3000);
                    }
                </script>
            </div>
        </div>
    </div>
@endsection