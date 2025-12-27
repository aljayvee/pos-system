{{-- Logic: If role is Cashier OR we passed 'context=cashier' in URL, use Cashier Layout --}}
@extends( (Auth::user()->role == 'cashier' || request('context') == 'cashier') ? 'cashier.layout' : 'admin.layout' )

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i> My Profile</h5>
                    
                    {{-- FIXED: Back Button Logic based on Context --}}
                    <a href="{{ (Auth::user()->role == 'cashier' || request('context') == 'cashier') ? route('cashier.pos') : route('admin.dashboard') }}" 
                       class="btn btn-sm btn-light text-primary fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin me-2\'></i> Updating...';">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-danger mb-3"><i class="fas fa-lock me-1"></i> Security</h6>

                        <div class="mb-3">
                            <label class="form-label">Current Password <small class="text-muted">(Required to change password)</small></label>
                            <input type="password" name="current_password" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-primary mb-3"><i class="fas fa-fingerprint me-1"></i> Biometric & Passkey Authentication</h6>
                        
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3 mb-3">
                            <div>
                                <h6 class="mb-1 fw-bold">Register Passkey</h6>
                                <p class="mb-0 text-muted small">Enable login with Fingerprint, FaceID, PIN, or Windows Hello.</p>
                            </div>
                            <button type="button" onclick="WebAuthn.register()" class="btn btn-outline-primary rounded-pill">
                                <i class="fas fa-plus me-1"></i> Valid Device
                            </button>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection