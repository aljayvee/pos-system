@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="m-0"><i class="fas fa-user-edit me-2"></i>Edit User Account</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        {{-- Role --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                                <option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier (POS Only)</option>
                            </select>
                        </div>

                        <hr>

                        {{-- Password Reset (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Reset Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only fill this if you want to change the user's password.</small>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fas fa-save me-1"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection