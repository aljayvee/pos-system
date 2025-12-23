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

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="e.g. Juan Cashier" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-lg bg-light border-0" placeholder="user@pos.com" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select form-select-lg bg-light border-0">
                                    <option value="cashier">Cashier (POS & Sales Only)</option>
                                    <option value="admin">Admin (Full System Access)</option>
                                </select>
                                <div class="form-text mt-2"><i class="fas fa-info-circle me-1 text-primary"></i> Admins can manage inventory, users, and settings. Cashiers can only process sales.</div>
                            </div>

                            <div class="col-12 my-2"><hr class="text-muted opacity-25"></div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control form-control-lg bg-light border-0" required minlength="6">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control form-control-lg bg-light border-0" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                            <a href="{{ route('users.index') }}" class="btn btn-light text-secondary rounded-pill px-4 fw-bold shadow-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg">
                                <i class="fas fa-save me-2"></i> Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection