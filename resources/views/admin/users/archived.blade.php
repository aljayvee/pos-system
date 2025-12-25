@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4 bg-light min-vh-100">
    <div class="row justify-content-center m-0">
        <div class="col-lg-10 p-0">

            {{-- MOBILE HEADER (Sticky) --}}
            <div class="d-flex d-lg-none align-items-center justify-content-between p-3 bg-white shadow-sm sticky-top" style="z-index: 1020;">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('users.index') }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-arrow-left text-dark"></i>
                    </a>
                    <h5 class="mb-0 fw-bold">Archived Users</h5>
                </div>
            </div>

            {{-- DESKTOP HEADER --}}
            <div class="d-none d-lg-flex align-items-center justify-content-between mb-4 mt-3">
                <h3 class="fw-bold text-dark m-0 tracking-tight"><i class="fas fa-archive text-secondary me-2"></i>Archived Users</h3>
                <a href="{{ route('users.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back to Users
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mx-3 mx-lg-0 mt-3 mt-lg-0" role="alert">
                    {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mx-3 mx-lg-0 mt-3 mt-lg-0" role="alert">
                    {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-0 rounded-lg-4 mb-4 overflow-hidden">
                
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
                                            <button onclick="confirmRestore({{ $user->id }})" class="btn btn-sm btn-success text-white rounded-pill px-3" title="Restore"><i class="fas fa-trash-restore me-1"></i> Restore</button>
                                            
                                            <form id="force-delete-form-{{ $user->id }}" action="{{ route('users.force_delete', $user->id) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                                            <button onclick="confirmForceDelete({{ $user->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="fas fa-times me-1"></i> Delete</button>
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

                {{-- MOBILE LIST VIEW --}}
                <div class="d-lg-none">
                    <div class="list-group list-group-flush">
                         @forelse($users as $user)
                            <div class="list-group-item p-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                     <div class="d-flex align-items-center gap-3">
                                        {{-- Avatar --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                            style="width: 42px; height: 42px; background: linear-gradient(135deg, #6c757d, #adb5bd); font-size: 1rem;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $user->name }}</h6>
                                            <small class="text-muted" style="font-size: 0.8rem;">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                    {{-- Role Badge --}}
                                     <span class="badge border rounded-pill text-uppercase {{ $user->role == 'admin' ? 'bg-danger-subtle text-danger border-danger' : 'bg-secondary-subtle text-secondary border-secondary' }}" style="font-size: 0.65rem;">
                                        {{ $user->role }}
                                    </span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="far fa-clock me-1"></i> {{ $user->deleted_at->format('M d, Y') }}
                                    </small>
                                    
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('users.restore', $user->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-light text-success border fw-bold rounded-pill px-3 shadow-sm" title="Restore">
                                                <i class="fas fa-trash-restore me-1"></i> Restore
                                            </button>
                                        </form>
                                        <form action="{{ route('users.force_delete', $user->id) }}" method="POST" onsubmit="return confirm('Permanent Delete?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-light text-danger border fw-bold rounded-pill px-3 shadow-sm" title="Delete Forever">
                                                <i class="fas fa-times me-1"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                         @empty
                            <div class="text-center py-5">
                                <div class="text-muted mb-3"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                <h6 class="fw-bold text-secondary">No Archived Users</h6>
                            </div>
                         @endforelse
                    </div>
                </div>
                
                @if(method_exists($users, 'links'))
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
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

