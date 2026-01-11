<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New User - VeraPOS</title>

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/premium-ui.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-body: #f3f4f6;
            --bg-card: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --accent-color: #007aff;
        }

        html.dark {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --border-color: #374151;
            --primary-color: #6366f1;
            --primary-hover: #818cf8;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
        }

        /* Native Header */
        .native-header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .header-title {
            font-weight: 600;
            font-size: 17px;
        }

        .header-back {
            position: absolute;
            left: 16px;
            color: var(--accent-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 17px;
        }

        /* Container */
        .page-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        /* Container - Compact Desktop Width */
        .page-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 0;
        }

        /* Facebook-like Card */
        .fb-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            padding: 24px;
        }

        .fb-header {
            border-bottom: 1px solid #dadde1;
            margin-bottom: 24px;
            padding-bottom: 16px;
        }

        .fb-title {
            font-size: 32px;
            font-weight: 700;
            line-height: 38px;
            color: #1c1e21;
            margin-bottom: 0;
        }

        .fb-subtitle {
            font-size: 15px;
            line-height: 24px;
            color: #606770;
        }

        /* Inputs */
        .form-control,
        .form-select {
            background-color: #f5f6f7 !important;
            border: 1px solid #ccd0d5 !important;
            border-radius: 5px;
            padding: 10px;
            font-size: 15px;
            color: #1c1e21 !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ccd0d5 !important;
            box-shadow: none !important;
            background-color: #fff !important;
        }

        .form-label-fb {
            font-size: 11px;
            color: #606770;
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }

        /* Gender Radio Boxes */
        .gender-box {
            border: 1px solid #ccd0d5;
            border-radius: 5px;
            padding: 0 8px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            cursor: pointer;
        }

        .form-check-input {
            margin-top: 0;
            float: none;
            margin-left: 5px;
        }

        /* Button */
        .btn-fb-blue {
            background-color: #4f46e5;
            /* Calm Blue / Indigo-600 */
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 700;
            padding: 8px 32px;
            color: #fff;
            width: 200px;
            margin: 0 auto;
            display: block;
        }

        .btn-fb-blue:hover {
            background-color: #4338ca;
            /* Indigo-700 */
            color: #fff;
        }

        .terms-text {
            font-size: 11px;
            color: #777;
            margin-top: 20px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Dark Mode */
        html.dark .fb-card {
            background: #242526;
        }

        html.dark .fb-title {
            color: #e4e6eb;
        }

        html.dark .fb-subtitle {
            color: #b0b3b8;
        }

        html.dark .form-control,
        html.dark .form-select {
            background-color: #3a3b3c !important;
            border-color: #3e4042 !important;
            color: #e4e6eb !important;
        }

        html.dark .gender-box {
            background: #3a3b3c;
            border-color: #3e4042;
            color: #e4e6eb;
        }

        html.dark .fb-header {
            border-bottom-color: #3e4042;
        }

        html.dark .form-label-fb {
            color: #b0b3b8;
        }

        /* MOBILE RESPONSIVE Native App View */
        @media (max-width: 768px) {
            .page-container {
                max-width: 100%;
                margin: 0;
            }

            .fb-card {
                box-shadow: none;
                border-radius: 0;
                padding: 16px;
                min-height: calc(100vh - 51px);
                background: transparent;
                /* Let native background show or keep white */
            }

            /* On mobile, usually cards are full screen white */
            body,
            .page-container,
            .fb-card {
                background-color: var(--bg-card) !important;
            }

            .fb-title {
                font-size: 24px;
                line-height: 28px;
            }

            .btn-fb-blue {
                width: 100%;
                /* Full width button */
            }

            /* Prevent iOS Zoom on inputs */
            .form-control,
            .form-select {
                font-size: 16px !important;
            }

            /* Adjust Native Header for Mobile */
            .native-header .header-title {
                display: block;
            }
        }

        /* Dark Mode Mobile Adjustments */
        @media (max-width: 768px) {

            html.dark body,
            html.dark .page-container,
            html.dark .fb-card {
                background-color: #18191a !important;
                /* FB Dark Mobile */
            }
        }

        /* Desktop Zoom View (80%) */
        @media (min-width: 992px) {
            body {
                zoom: 80%;
            }
        }
    </style>
</head>

<body>

    {{-- NATIVE HEADER --}}
    <div class="native-header">
        <div class="header-content" style="max-width: 900px; margin: 0 auto;">
            <a href="{{ route('users.index') }}" class="header-back">
                <i class="fas fa-chevron-left me-1"></i> Back
            </a>
            <div class="header-title">Create Account</div>
            <div style="width: 60px;"></div>
        </div>
    </div>

    <div class="page-container">

        <div class="fb-card">

            <div class="fb-header text-center text-md-start">
                <h2 class="fb-title">Sign Up</h2>
                <div class="fb-subtitle">It's quick and easy.</div>
            </div>

            <form id="createUserForm" action="{{ route('users.store') }}" method="POST">
                @csrf

                <div id="stepForm">

                    {{-- Row 1: Names --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="first_name" class="form-control" required placeholder="First name">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="middle_name" class="form-control" placeholder="Middle name">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="last_name" class="form-control" required placeholder="Surname">
                        </div>
                    </div>

                    {{-- Row 2: Contact & Access --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" required placeholder="Email address">
                        </div>
                        <div class="col-md-6">
                            <select name="role" class="form-select">
                                <option value="" disabled selected>Select Role</option>
                                @if(auth()->user()->role === 'admin')
                                    <option value="admin">Admin</option>
                                @endif
                                <option value="manager">Manager</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="cashier">Cashier</option>
                                <option value="stock_clerk">Stock Clerk</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row 3: Security --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="password" name="password" class="form-control" required minlength="8"
                                placeholder="New password">
                        </div>
                        <div class="col-md-6">
                            <input type="password" name="password_confirmation" class="form-control" required
                                placeholder="Confirm password">
                        </div>
                    </div>

                    {{-- Row 4: Personal (DOB & Gender) --}}
                    <div class="row g-2 mb-3">
                        {{-- DOB --}}
                        <div class="col-md-5">
                            <label class="form-label-fb">Date of birth</label>
                            <input type="date" name="birthdate" class="form-control">
                        </div>
                        {{-- Gender --}}
                        <div class="col-md-7">
                            <label class="form-label-fb">Gender</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="gender-box">
                                        <span class="small">Female</span>
                                        <input class="form-check-input" type="radio" name="gender" value="Female">
                                    </label>
                                </div>
                                <div class="col-4">
                                    <label class="gender-box">
                                        <span class="small">Male</span>
                                        <input class="form-check-input" type="radio" name="gender" value="Male">
                                    </label>
                                </div>
                                <div class="col-4">
                                    <label class="gender-box">
                                        <span class="small">Custom</span>
                                        <input class="form-check-input" type="radio" name="gender" value="Other">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 5: Store (Only if applicable) --}}
                    @if(auth()->user()->role === 'admin' && config('safety_flag_features.multi_store'))
                        <div class="mb-3">
                            <label class="form-label-fb">Assigned Branch</label>
                            <select name="store_id" class="form-select">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }} {{ $store->id == 1 ? '(HQ)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <p class="terms-text text-center">
                        People who use our service may have uploaded your contact information to VeraPOS. <a href="#"
                            class="text-decoration-none">Learn more</a>.
                        <br><br>
                        By clicking Sign Up, you agree to our <a href="#" class="text-decoration-none">Terms</a>, <a
                            href="#" class="text-decoration-none">Privacy Policy</a> and <a href="#"
                            class="text-decoration-none">Cookies Policy</a>.
                    </p>

                    <div class="text-center">
                        <button type="submit" id="btnCreate" class="btn-fb-blue">Sign Up</button>
                    </div>

                </div>

                {{-- ERRORS --}}
                @if($errors->any())
                    <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
        </div>

        <!-- WAITING SCREEN -->
        <div id="stepWaiting" class="d-none text-center py-5">
            <div class="spinner-border text-primary my-4" style="width: 3rem; height: 3rem;" role="status"></div>
            <h4 class="fw-bold">Waiting for Approval...</h4>
            <p class="text-muted mb-4">An Admin must approve this new account creation.</p>
            <div class="alert alert-warning d-inline-block px-4 border-0 rounded-pill shadow-sm">
                <i class="fas fa-clock me-2"></i> Please check with an Administrator.
            </div>
        </div>

        </form>
    </div>

    <!-- ADMIN SELECTION MODAL -->
    <div class="modal fade" id="adminSelectModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: var(--bg-card); color: var(--text-main);">
                <div class="modal-header border-0 bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-shield-alt me-2"></i>Approval Required</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <p class="text-muted small">As a Manager, you need an Administrator's approval to create a new
                            account.</p>
                    </div>
                    <div class="form-floating mb-4 text-start">
                        <select class="form-select border fw-bold" id="approverId"
                            style="background: var(--bg-body); color: var(--text-main);">
                            <option value="" selected disabled>Select an Admin...</option>
                            @if(isset($admins))
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <label for="approverId">Select Approver</label>
                    </div>
                    <button type="button" id="btnConfirmRequest" class="btn-primary-custom">Send Request</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                        e.preventDefault(); e.stopPropagation();
                        if (approvalModal) approvalModal.show();
                    } else {
                        e.preventDefault(); submitForm(form);
                    }
                });
            }

            const btnConfirm = document.getElementById('btnConfirmRequest');
            if (btnConfirm) {
                btnConfirm.addEventListener('click', function () {
                    const approverId = document.getElementById('approverId').value;
                    if (!approverId) { Swal.fire('Error', 'Please select an administrator.', 'error'); return; }
                    if (approvalModal) approvalModal.hide();
                    submitForm(form, approverId);
                });
            }
        });

        function submitForm(form, approverId = null) {
            const stepForm = document.getElementById('stepForm');
            const stepWaiting = document.getElementById('stepWaiting');
            const btn = document.getElementById('btnCreate');

            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...'; }

            const formData = new FormData(form);
            if (approverId) formData.append('approver_id', approverId);
            const data = Object.fromEntries(formData.entries());

            fetch(form.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(data)
            }).then(res => res.json()).then(resp => {
                if (resp.pending_approval) {
                    stepForm.classList.add('d-none');
                    stepWaiting.classList.remove('d-none');
                    startPolling(resp.request_id);
                } else if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Success', text: 'User created successfully.', timer: 1500, showConfirmButton: false })
                        .then(() => { window.location.href = "{{ route('users.index') }}"; });
                } else { throw new Error(resp.message || 'Failed to create user.'); }
            }).catch(err => {
                Swal.fire('Error', err.message || 'An error occurred.', 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = 'Create Account'; }
            });
        }

        function startPolling(requestId) {
            const pollInterval = setInterval(() => {
                fetch(`/admin/approval/${requestId}/status`).then(res => res.json()).then(data => {
                    if (data.status === 'approved') {
                        clearInterval(pollInterval);
                        Swal.fire({ title: 'Approved!', text: 'Account activated.', icon: 'success', timer: 2000, showConfirmButton: false })
                            .then(() => { window.location.href = "{{ route('users.index') }}"; });
                    } else if (data.status === 'rejected') {
                        clearInterval(pollInterval);
                        Swal.fire({ title: 'Rejected', text: 'Request denied.', icon: 'error' }).then(() => { window.location.reload(); });
                    }
                }).catch(e => console.error(e));
            }, 3000);
        }
    </script>
</body>

</html>