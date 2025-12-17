@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-users-cog text-primary me-2"></i>Users</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm"><i class="fas fa-user-plus me-1"></i> New User</a>
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
                            <th class="py-3">Status</th>
                            <th class="py-3">Created</th>
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
                                <span class="badge {{ $user->role == 'admin' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-info-subtle text-info-emphasis border border-info' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <form action="{{ route('users.toggle', $user->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary" title="Toggle"><i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i></button>
                                    </form>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning text-dark"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- === MOBILE NATIVE VIEW === --}}
        <div class="d-lg-none">
            @forelse($users as $user)
            <div class="card shadow-sm border-0 mb-3 mx-3 mt-3" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $user->name }}</div>
                            <div class="small text-muted mb-2">{{ $user->email }}</div>
                        </div>
                        <span class="badge {{ $user->role == 'admin' ? 'bg-danger' : 'bg-info text-dark' }} rounded-pill">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <span class="badge rounded-pill {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        
                        <div class="d-flex gap-2">
                            <form action="{{ route('users.toggle', $user->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-light border shadow-sm text-secondary rounded-circle" style="width: 36px; height: 36px;"><i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i></button>
                            </form>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-light border shadow-sm text-warning rounded-circle" style="width: 36px; height: 36px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-light border shadow-sm text-danger rounded-circle" style="width: 36px; height: 36px;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">No users found.</div>
            @endforelse
        </div>
        
        @if(method_exists($users, 'links'))
        <div class="card-footer bg-white border-top-0 py-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection