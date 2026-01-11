<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-context"
        content="{{ (Auth::user()->role == 'cashier' || request('context') == 'cashier') ? 'cashier' : 'admin' }}">
    <title>My Profile - VeraPOS</title>

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
            --sidebar-width: 280px;
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

        /* Native Header (Preserved for Consistency) */
        .native-header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        .header-back {
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .header-back:hover {
            color: var(--primary-color);
        }

        .header-title {
            font-weight: 700;
            font-size: 18px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Desktop Layout Container */
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Sidebar & Navigation */
        .settings-sidebar {
            background: transparent;
        }

        .nav-settings .nav-link {
            color: var(--text-muted);
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-settings .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            font-size: 16px;
        }

        .nav-settings .nav-link:hover {
            background: rgba(79, 70, 229, 0.05);
            /* Light Indigo */
            color: var(--primary-color);
        }

        .nav-settings .nav-link.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        /* Content Cards */
        .settings-card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .card-header-custom {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title-custom {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: var(--text-main);
        }

        .card-body-custom {
            padding: 24px;
        }

        /* Forms */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control,
        .form-select {
            background-color: var(--bg-body);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 15px;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--bg-card);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .form-control[readonly] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Buttons */
        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-custom {
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            background: transparent;
            transition: all 0.2s;
        }

        .btn-outline-custom:hover {
            background: var(--bg-body);
            border-color: var(--text-muted);
        }

        /* Profile Avatar in Header */
        .user-header-block {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .large-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--bg-card);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Dark Mode Overrides */
        html.dark .form-control,
        html.dark .form-select {
            background-color: #0f172a;
            /* darker bg for inputs in dark mode */
            border-color: #374151;
        }

        html.dark .form-control:focus {
            background-color: #1e293b;
        }

        html.dark .nav-settings .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        @media (max-width: 768px) {
            .main-container {
                display: flex;
                flex-direction: column;
            }

            .sidebar-col {
                margin-bottom: 20px;
            }
        }
        
        /* Desktop Zoom View (80%) */
        @media (min-width: 992px) {
            body {
                zoom: 80%;
            }
        }
        
        /* Tab Animation */
        .content-tab {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="native-header">
        <div class="header-content">
            <a href="{{ (Auth::user()->role == 'cashier' || request('context') == 'cashier') ? route('cashier.pos') : route('admin.dashboard') }}"
                class="header-back">
                <i class="fas fa-arrow-left me-2"></i> Dashboard
            </a>
            <div class="header-title">Account Settings</div>
        </div>
    </div>

    <div class="main-container">

        {{-- USER SUMMARY HEADER --}}
        <div class="user-header-block px-3">
            <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&size=200' }}"
                class="large-avatar me-4">
            <div>
                <h2 class="fw-bold mb-1" style="font-size: 24px;">{{ $user->name }}</h2>
                <p class="text-muted mb-0">{{ $user->email }} • <span
                        class="badge bg-primary bg-opacity-10 text-primary">{{ ucfirst($user->role) }}</span></p>
            </div>
        </div>

        <div class="row g-4">

            {{-- SIDEBAR NAVIGATION --}}
            <div class="col-md-3 sidebar-col d-none d-lg-block">
                <div class="settings-sidebar sticky-top" style="top: 80px;">
                    <nav class="nav nav-settings flex-column">
                        <a class="nav-link active" href="#" onclick="switchTab('general', this)">
                            <i class="fas fa-user-circle"></i> General
                        </a>
                        <a class="nav-link" href="#" onclick="switchTab('security', this)">
                            <i class="fas fa-shield-alt"></i> Security
                        </a>
                        <!-- Logout -->
                        
                    </nav>
                </div>
            </div>

            {{-- CONTENT AREA --}}
            <div class="col-12 col-lg-9">
            
                {{-- MOBILE NAVIGATION (Segmented Control) --}}
                <div class="d-lg-none mb-3">
                    <div class="p-1 rounded-3 d-flex" style="background: rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
                        <button class="btn btn-sm flex-fill fw-bold rounded-2 btn-mobile-tab active bg-primary text-white shadow-sm" 
                            id="mobile-tab-general"
                            onclick="switchTab('general', this, true)"
                            style="transition: all 0.2s; border: none; height: 32px;">
                            General
                        </button>
                        <button class="btn btn-sm flex-fill fw-bold rounded-2 btn-mobile-tab text-muted" 
                            id="mobile-tab-security"
                            onclick="switchTab('security', this, true)"
                            style="transition: all 0.2s; border: none; height: 32px;">
                            Security
                        </button>
                    </div>
                </div>

                {{-- TAB: GENERAL --}}
                <div id="tab-general" class="content-tab">
                    
                    {{-- 1. PERSONAL INFORMATION --}}
                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="card-title-custom">Personal Information</h5>
                            <form id="photo-form" action="{{ route('profile.update.photo') }}" method="POST" enctype="multipart/form-data"
                                class="d-inline">
                                @csrf
                                <input type="file" name="photo" id="photo-upload" class="d-none" accept="image/*"
                                    onchange="previewPhoto(this)">
                                <label for="photo-upload" class="btn btn-sm btn-outline-custom" style="cursor: pointer;">
                                    <i class="fas fa-camera me-2"></i>Change Photo
                                </label>
                            </form>
                        </div>
                        <div class="card-body-custom">
                            <form action="{{ route('profile.update.info') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select Gender</option>
                                            <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>Female
                                            </option>
                                            <option value="Other" {{ $user->gender == 'Other' ? 'selected' : '' }}>Other
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" name="birthdate" class="form-control"
                                            value="{{ $user->birthdate }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <input type="text" class="form-control" value="{{ $user->age ?? 'N/A' }}" readonly>
                                    </div>

                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary-custom px-4">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- 2. CONTACT DETAILS --}}
                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="card-title-custom">Contact Details</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label class="form-label">Email Address</label>
                                    <div class="d-flex align-items-center">
                                        <input type="email" class="form-control flex-grow-1 me-2" value="{{ $user->email }}"
                                            readonly>
                                        @if(!$user->hasVerifiedEmail())
                                            <span class="badge bg-warning text-dark">Unverified</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success"><i
                                                    class="fas fa-check me-1"></i> Verified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4 text-end mt-3 mt-md-0">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <button class="btn btn-outline-custom"
                                        onclick="new bootstrap.Modal(document.getElementById('emailChangeModal')).show()">
                                        Change Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TAB: SECURITY --}}
                <div id="tab-security" class="content-tab d-none">
                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="card-title-custom">Security Settings</h5>
                        </div>
                        <div class="card-body-custom">

                            {{-- Password Change --}}
                            <div class="mb-5">
                                <h6 class="fw-bold mb-3"><i class="fas fa-lock me-2 text-primary"></i>Change Password</h6>
                                <form action="{{ route('profile.update.security') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <input type="password" name="current_password" class="form-control"
                                                placeholder="Current Password" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="password" name="password" class="form-control"
                                                placeholder="New Password" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="password" name="password_confirmation" class="form-control"
                                                placeholder="Confirm New Password" required>
                                        </div>
                                        <div class="col-12 text-end mt-2">
                                            <button type="submit" class="btn btn-sm btn-primary-custom">Update
                                                Password</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <hr class="border-secondary border-opacity-10 my-4">

                            {{-- MPIN Change --}}
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-key me-2 text-success"></i>MPIN Setup
                                    <span
                                        class="badge {{ $hasMpin ? 'bg-success' : 'bg-secondary' }} ms-2">{{ $hasMpin ? 'Active' : 'Not Set' }}</span>
                                </h6>
                                <form action="{{ route('profile.update.security') }}" method="POST">
                                    @csrf
                                    <p class="text-muted small">Enter your current standard password to authorize MPIN
                                        changes.</p>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="password" name="current_password" class="form-control"
                                                placeholder="Account Password (Required)" required>
                                        </div>
                                        @if($hasMpin)
                                            <div class="col-md-6">
                                                <input type="password" name="current_mpin" class="form-control"
                                                    placeholder="Current MPIN" inputmode="numeric">
                                            </div>
                                        @endif
                                        <div class="col-md-6">
                                            <input type="password" name="mpin" class="form-control"
                                                placeholder="New MPIN (6 digits)" inputmode="numeric">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" name="mpin_confirmation" class="form-control"
                                                placeholder="Confirm MPIN" inputmode="numeric">
                                        </div>

                                        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                                            <a href="{{ route('auth.mpin.forgot') }}"
                                                class="small text-decoration-none">Forgot MPIN?</a>
                                            <button type="submit" class="btn btn-sm btn-primary-custom">Save MPIN</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            @if(config('safety_flag_features.webauthn'))
                                <hr class="border-secondary border-opacity-10 my-4">
                                <div>
                                    <h6 class="fw-bold mb-3"><i class="fas fa-fingerprint me-2 text-purple-500"></i>Biometrics
                                    </h6>
                                    <div
                                        class="d-flex justify-content-between align-items-center bg-body p-3 rounded-3 border border-color">
                                        <div>
                                            <p class="mb-0 fw-medium">FaceID / TouchID</p>
                                            <p class="text-muted small mb-0">Use your device biometrics for faster login.</p>
                                        </div>
                                        <button type="button" onclick="WebAuthn.register()"
                                            class="btn btn-sm btn-outline-custom">Register Device</button>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- EMAIL CHANGE MODAL (Retained) --}}
    <div class="modal fade" id="emailChangeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg"
                style="border-radius: 16px; overflow: hidden; background: var(--bg-card); color: var(--text-main);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Change Email Address</h5>
                    <button type="button" class="btn-close {{ Auth::user()->role == 'admin' ? '' : 'btn-close-white' }}"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div id="email-wizard-container">
                        <p class="small text-muted mb-3">
                            <i class="fas fa-shield-alt me-1 text-primary"></i>
                            For security, changing your email requires verifying your identity.
                        </p>

                        {{-- Step 1: Verify Password --}}
                        <div id="email-step-1" class="email-step">
                            <label class="form-label small fw-bold">Step 1: Verify Password</label>
                            <div class="input-group mb-2">
                                <input type="password" id="current-password-verify" class="form-control"
                                    placeholder="Enter Account Password" required>
                                <button type="button" class="btn btn-primary-custom" onclick="initiateEmailChange()">
                                    <span class="d-none spinner-border spinner-border-sm me-1" role="status"
                                        aria-hidden="true"></span>
                                    Verify
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Verify Current OTP --}}
                        <div id="email-step-2" class="email-step d-none">
                            <div class="alert alert-info py-2 px-3 small border-0 mb-3 rounded-3">
                                <i class="fas fa-envelope me-1"></i> OTP sent to <span
                                    class="fw-bold">{{ $user->email }}</span>
                            </div>
                            <label class="form-label small fw-bold">Step 2: Enter OTP from Current Email</label>
                            <div class="input-group mb-2">
                                <input type="text" id="otp-current" class="form-control" placeholder="6-digit Code"
                                    maxlength="6">
                                <button type="button" class="btn btn-primary-custom"
                                    onclick="verifyCurrentEmailOtp()">Verify OTP</button>
                            </div>
                        </div>

                        {{-- Step 3: Enter New Email --}}
                        <div id="email-step-3" class="email-step d-none">
                            <label class="form-label small fw-bold">Step 3: Enter New Email Address</label>
                            <div class="input-group mb-2">
                                <input type="email" id="new-email-input" class="form-control"
                                    placeholder="new.email@example.com">
                                <button type="button" class="btn btn-primary-custom" onclick="requestNewEmailOtp()">Send
                                    OTP</button>
                            </div>
                        </div>

                        {{-- Step 4: Verify New Email --}}
                        <div id="email-step-4" class="email-step d-none">
                            <div class="alert alert-info py-2 px-3 small border-0 mb-3 rounded-3">
                                <i class="fas fa-envelope-open me-1"></i> OTP sent to new email address.
                            </div>
                            <label class="form-label small fw-bold">Step 4: Enter OTP from New Email</label>
                            <div class="input-group mb-2">
                                <input type="text" id="otp-new" class="form-control" placeholder="6-digit Code"
                                    maxlength="6">
                                <button type="button" class="btn btn-primary-custom" onclick="confirmNewEmail()">Confirm
                                    Change</button>
                            </div>
                        </div>

                        {{-- Success Message --}}
                        <div id="email-step-success" class="email-step d-none text-center py-4">
                            <div class="mb-3">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px;">
                                    <i class="fas fa-check text-success fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="h5 fw-bold mb-2">Email Updated!</h4>
                            <p class="small text-muted mb-4">Your email address has been successfully changed.</p>
                            <button class="btn btn-primary-custom w-100 rounded-pill"
                                onclick="location.reload()">Refresh Page</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTS --}}
    {{-- PHOTO PREVIEW MODAL --}}
    <div class="modal fade" id="photoPreviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: var(--bg-card); color: var(--text-main);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-center w-100">Preview Photo</h5>
                </div>
                <div class="modal-body text-center pt-3">
                    <div class="position-relative d-inline-block">
                        <img id="photo-preview-img" src="" class="rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--bg-body);">
                    </div>
                    <p class="small text-muted mt-3 mb-0">Does this look good?</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
                    <button type="button" class="btn btn-outline-custom btn-sm px-4" data-bs-dismiss="modal" onclick="resetPhotoInput()">Cancel</button>
                    <button type="button" class="btn btn-primary-custom btn-sm px-4" onclick="submitPhoto()">Upload</button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 p-3 rounded-pill bg-dark text-white shadow-lg fade-in"
            style="z-index: 2000; min-width: 200px; text-align: center;">
            <i class="fas fa-check-circle me-2 text-success"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="position-fixed bottom-0 start-50 translate-middle-x mb-4 p-3 rounded-3 bg-danger text-white shadow-lg"
            style="z-index: 2000; width: 90%; max-width: 400px;">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Photo Preview Logic
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview-img').src = e.target.result;
                    new bootstrap.Modal(document.getElementById('photoPreviewModal')).show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function submitPhoto() {
            document.getElementById('photo-form').submit();
        }
        
        function resetPhotoInput() {
            document.getElementById('photo-upload').value = '';
        }
        
        // Tab Switcher
        function switchTab(tabId, el, isMobile = false) {
            // Hide all tabs
            document.querySelectorAll('.content-tab').forEach(tab => tab.classList.add('d-none'));
            // Show target tab
            const target = document.getElementById('tab-' + tabId);
            if(target) target.classList.remove('d-none');
            
            // Sync Desktop Sidebar
            document.querySelectorAll('.nav-settings .nav-link').forEach(link => {
                link.classList.remove('active');
                if(!isMobile && link === el) link.classList.add('active');
                // Auto-highlight corresponding desktop link if triggered from mobile
                if(isMobile && link.getAttribute('onclick').includes(tabId)) link.classList.add('active');
            });

            // Sync Mobile Tabs
            document.querySelectorAll('.btn-mobile-tab').forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-white', 'shadow-sm');
                btn.classList.add('text-muted');
                
                // If this is the button clicked OR if it corresponds to the desktop click
                if((isMobile && btn === el) || (!isMobile && btn.id === 'mobile-tab-' + tabId)) {
                    btn.classList.remove('text-muted');
                    btn.classList.add('active', 'bg-primary', 'text-white', 'shadow-sm');
                }
            });
        }

        // ==========================================
        // SECURE EMAIL CHANGE WIZARD JS (Retained)
        // ==========================================

        function showStep(stepNumber) {
            document.querySelectorAll('.email-step').forEach(el => el.classList.add('d-none'));
            document.getElementById('email-step-' + stepNumber).classList.remove('d-none');
        }

        // Step 1: Verify Password
        function initiateEmailChange() {
            const password = document.getElementById('current-password-verify').value;
            const btn = document.querySelector('#email-step-1 button');
            const spinner = btn.querySelector('.spinner-border');

            if (!password) { alert('Please enter your password.'); return; }

            btn.disabled = true; spinner.classList.remove('d-none');

            fetch('{{ route("profile.email.initiate") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: password })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    showStep(2);
                } else {
                    alert(data.message);
                }
            }).catch(err => alert('Error connecting to server.'))
                .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
        }

        // Step 2: Verify Current OTP
        function verifyCurrentEmailOtp() {
            const otp = document.getElementById('otp-current').value;
            const btn = document.querySelector('#email-step-2 button');

            if (!otp) return;
            btn.disabled = true; btn.innerText = 'Verifying...';

            fetch('{{ route("profile.email.verify_current") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp: otp })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    showStep(3);
                } else {
                    alert(data.message);
                }
            }).catch(err => alert('Connection error'))
                .finally(() => { btn.disabled = false; btn.innerText = 'Verify OTP'; });
        }

        // Step 3: Request New OTP
        function requestNewEmailOtp() {
            const email = document.getElementById('new-email-input').value;
            const btn = document.querySelector('#email-step-3 button');

            if (!email) return;
            btn.disabled = true; btn.innerText = 'Sending...';

            fetch('{{ route("profile.email.request_new") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ new_email: email })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    showStep(4);
                } else {
                    alert(data.message);
                }
            }).catch(err => alert('Connection error'))
                .finally(() => { btn.disabled = false; btn.innerText = 'Send OTP'; });
        }

        // Step 4: Confirm Change
        function confirmNewEmail() {
            const otp = document.getElementById('otp-new').value;
            const btn = document.querySelector('#email-step-4 button');

            if (!otp) return;
            btn.disabled = true; btn.innerText = 'Finalizing...';

            fetch('{{ route("profile.email.confirm_update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp: otp })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    document.querySelectorAll('.email-step').forEach(el => el.classList.add('d-none'));
                    document.getElementById('email-step-success').classList.remove('d-none');
                } else {
                    alert(data.message);
                }
            }).catch(err => alert('Connection error'))
                .finally(() => { btn.disabled = false; btn.innerText = 'Confirm Change'; });
        }
    </script>
</body>

</html>