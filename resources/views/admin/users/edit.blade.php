@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between mt-4 mb-4">
        <h1 class="h2 mb-0 text-gray-800">Edit User</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Update Account: {{ $user->name }}</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Role</label>
                                <select name="role" class="form-select bg-light">
                                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                                    <option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier (POS Only)</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-light border mt-4">
                            <h6 class="text-danger fw-bold"><i class="fas fa-lock me-1"></i> Security</h6>
                            <p class="small text-muted mb-2">Leave the field below blank if you do NOT want to change the password.</p>
                            
                            <label class="form-label fw-bold small text-uppercase">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter new password to reset...">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-light text-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning px-4 fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection