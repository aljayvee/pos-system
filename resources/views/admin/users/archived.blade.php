@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-archive text-secondary me-2"></i>Archived Users</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary shadow-sm"><i class="fas fa-arrow-left me-1"></i> Back to Users</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        
        {{-- DESKTOP TABLE --}}
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">User</th>
                            <th class="py-3">Role</th>
                            <th class="py-3">Deleted At</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $user->role == 'admin' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-secondary-subtle text-secondary border border-secondary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $user->deleted_at->format('M d, Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <form id="restore-form-{{ $user->id }}" action="{{ route('users.restore', $user->id) }}" method="POST" style="display:none;">@csrf</form>
                                    <button onclick="confirmRestore({{ $user->id }})" class="btn btn-sm btn-success text-white" title="Restore"><i class="fas fa-trash-restore"></i> Restore</button>
                                    
                                    <form id="force-delete-form-{{ $user->id }}" action="{{ route('users.force_delete', $user->id) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                                    <button onclick="confirmForceDelete({{ $user->id }})" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Permanent Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted">No archived users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE VIEW --}}
        <div class="d-lg-none">
            @forelse($users as $user)
            <div class="card shadow-sm border-0 mb-3 mx-3 mt-3" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $user->name }}</div>
                            <div class="small text-muted mb-2">{{ $user->email }}</div>
                        </div>
                        <span class="badge {{ $user->role == 'admin' ? 'bg-danger' : 'bg-secondary' }} rounded-pill">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="text-muted small">Deleted: {{ $user->deleted_at->format('M d, Y') }}</span>
                        
                        <div class="d-flex gap-2">
                            <form action="{{ route('users.restore', $user->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-light border shadow-sm text-success rounded-circle" style="width: 36px; height: 36px;" title="Restore"><i class="fas fa-trash-restore"></i></button>
                            </form>
                            <form action="{{ route('users.force_delete', $user->id) }}" method="POST" onsubmit="return confirm('Permanent Delete?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 36px; height: 36px;" title="Review"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">No archived users found.</div>
            @endforelse
        </div>
        
        @if(method_exists($users, 'links'))
        <div class="card-footer bg-white border-top-0 py-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>

<script>
    function confirmRestore(userId) {
        Swal.fire({
            title: 'Restore User?',
            text: "User will be restored to active list.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restore-form-' + userId).submit();
            }
        });
    }

    function confirmForceDelete(userId) {
        Swal.fire({
            title: 'Permanently Delete?',
            text: "This cannot be undone! Data will be lost forever.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, verify delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('force-delete-form-' + userId).submit();
            }
        });
    }
</script>
@endsection
