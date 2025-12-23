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
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                <select name="role" class="form-select bg-light border-0">
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                                    <option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier (POS Only)</option>
                                </select>
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
@endsection