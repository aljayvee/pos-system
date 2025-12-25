@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold text-dark m-0 tracking-tight">Create Account</h3>
                    <p class="text-muted small m-0">Add a new user to the system.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-plus me-2"></i>New User Details</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    @if($errors->any())
                        <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="createUserForm" action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div id="stepForm">
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control bg-light border-0" placeholder="e.g. Juan Cashier" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control bg-light border-0" placeholder="user@pos.com" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select bg-light border-0">
                                        @if(auth()->user()->role === 'admin')
                                        <option value="admin">Admin (Full System Access)</option>
                                        @endif
                                        <option value="manager">Manager (Operations & Overrides)</option>
                                        <option value="supervisor">Supervisor (Sales Management)</option>
                                        <option value="cashier">Cashier (POS & Sales)</option>
                                        <option value="stock_clerk">Stock Clerk (Inventory Only)</option>
                                        <option value="auditor">Auditor (Read-Only Access)</option>
                                    </select>
                                    <div class="form-text mt-2"><i class="fas fa-info-circle me-1 text-primary"></i> Select the primary role.</div>
                                </div>

                                <div class="col-12 my-2"><hr class="text-muted opacity-25"></div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control bg-light border-0" required minlength="6">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small text-uppercase text-secondary">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control bg-light border-0" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                                <a href="{{ route('users.index') }}" class="btn btn-light text-secondary rounded-pill px-4 fw-bold shadow-sm">Cancel</a>
                                <button type="submit" id="btnCreate" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg">
                                    <i class="fas fa-save me-2"></i> Create Account
                                </button>
                            </div>
                        </div>

                        <!-- WAITING SCREEN (Hidden by default) -->
                        <div id="stepWaiting" class="d-none text-center py-5">
                            <div class="spinner-border text-primary my-4" style="width: 3rem; height: 3rem;" role="status"></div>
                            <h4 class="fw-bold text-dark">Waiting for Approval...</h4>
                            <p class="text-muted mb-4">An Admin must approve this new account creation.</p>
                            <div class="alert alert-warning d-inline-block px-4 border-0 rounded-pill shadow-sm">
                                <i class="fas fa-clock me-2"></i> Please check with an Administrator.
                            </div>
                        </div>
                    </form>

                    <!-- ADMIN SELECTION MODAL -->
                    <div class="modal fade" id="adminSelectModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="modal-header bg-primary text-white border-0">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Approval Required</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-center">
                                    <div class="mb-4">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                                            <i class="fas fa-user-lock fa-2x"></i>
                                        </div>
                                        <p class="text-muted small">As a Manager, you need an Administrator's approval to create a new account.</p>
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
                                    
                                    <button type="button" id="btnConfirmRequest" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                        <i class="fas fa-paper-plane me-2"></i> Send Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.getElementById('createUserForm');
                        // PHP to JS Variable Injection
                        const isManager = {{ strtolower(auth()->user()->role) === 'manager' ? 'true' : 'false' }};
                        
                        let approvalModal = null;
                        if(typeof bootstrap !== 'undefined') {
                            const modalEl = document.getElementById('adminSelectModal');
                            if(modalEl) approvalModal = new bootstrap.Modal(modalEl);
                        }

                        if (form) {
                            form.addEventListener('submit', function(e) {
                                // If Manager, intercept submission
                                if (isManager) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    
                                    // Show Modal
                                    if (approvalModal) {
                                        approvalModal.show();
                                    } else {
                                        // Fallback manual show
                                        const modalEl = document.getElementById('adminSelectModal');
                                        if(modalEl) {
                                            modalEl.classList.add('show');
                                            modalEl.style.display = 'block';
                                            document.body.classList.add('modal-open');
                                            const backdrop = document.createElement('div');
                                            backdrop.className = 'modal-backdrop fade show';
                                            document.body.appendChild(backdrop);
                                        }
                                    }
                                } else {
                                    // Normal Admin Flow (AJAX for UX consistency)
                                    e.preventDefault();
                                    submitForm(form);
                                }
                            });
                        }

                        // Modal Confirm Button
                        const btnConfirm = document.getElementById('btnConfirmRequest');
                        if(btnConfirm) {
                            btnConfirm.addEventListener('click', function() {
                                const approverId = document.getElementById('approverId').value;
                                if (!approverId) {
                                    Swal.fire('Error', 'Please select an administrator.', 'error');
                                    return;
                                }
                                
                                if(approvalModal) approvalModal.hide();
                                else {
                                    // Hide manual modal
                                    const modalEl = document.getElementById('adminSelectModal');
                                    modalEl.classList.remove('show');
                                    modalEl.style.display = 'none';
                                    document.body.classList.remove('modal-open');
                                    const bd = document.querySelector('.modal-backdrop');
                                    if(bd) bd.remove();
                                }

                                submitForm(form, approverId);
                            });
                        }
                    });

                    function submitForm(form, approverId = null) {
                        const btn = document.getElementById('btnCreate');
                        const stepForm = document.getElementById('stepForm');
                        const stepWaiting = document.getElementById('stepWaiting');
                        
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
                        
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
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save me-2"></i> Create Account';
                            
                            // Reset modal if failed?
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
    </div>
</div>
@endsection