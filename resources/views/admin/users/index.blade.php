@extends('admin.layout')

@section('content')
{{-- WRAPPER FOR MOBILE-NATIVE FEEL --}}
<div class="container-fluid px-0 px-lg-4">
    
    {{-- DESKTOP HEADER (Hidden on Mobile) --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2 px-3 ps-lg-0">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-users-cog text-primary me-2"></i>Users</h1>
        @if(auth()->user()->role !== 'auditor')
        <div class="d-flex gap-2">
            <a href="{{ route('users.archived') }}" class="btn btn-outline-secondary shadow-sm"><i class="fas fa-archive me-1"></i> Archive</a>
            <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm"><i class="fas fa-user-plus me-1"></i> New User</a>
        </div>
        @endif
    </div>

    {{-- DESKTOP SEARCH --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4 d-none d-lg-block">
        <div class="card-body p-3">
            <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 py-2" 
                               placeholder="Search user name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-dark w-100 rounded-pill fw-bold py-2">Search</button>
                </div>
                @if(request('search'))
                <div class="col-12 col-md-2">
                    <a href="{{ route('users.index') }}" class="btn btn-light w-100 rounded-pill fw-bold py-2 border">Clear</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    {{-- MOBILE HEADER & SEARCH (Sticky) --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3" style="z-index: 1020;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0 fw-bold text-dark">Users</h1>
            @if(auth()->user()->role !== 'auditor')
            <a href="{{ route('users.archived') }}" class="text-secondary small text-decoration-none"><i class="fas fa-archive me-1"></i>Archive</a>
            @endif
        </div>
        {{-- Search Input --}}
        <form action="{{ route('users.index') }}" method="GET" class="position-relative">
            <i class="fas fa-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
            <input type="text" name="search" class="form-control form-control-lg bg-light border-0 ps-5 rounded-pill" placeholder="Search users..." style="font-size: 1rem;" value="{{ request('search') }}">
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm m-3" role="alert">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm m-3" role="alert">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- DESKTOP CARD VIEW --}}
    <div class="card shadow-sm border-0 mb-4 d-none d-lg-block">
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
                            <div class="small text-muted">{{ $user->username }}</div>
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
                                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                                <form action="{{ route('users.toggle', $user->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Toggle"><i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i></button>
                                </form>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning text-dark"><i class="fas fa-edit"></i></a>
                                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                                <button onclick="confirmDelete({{ $user->id }})" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                @else
                                <button class="btn btn-sm btn-light text-muted" disabled><i class="fas fa-lock"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($users, 'links'))
        <div class="card-footer bg-white border-top-0 py-3">{{ $users->links() }}</div>
        @endif
    </div>


    {{-- MOBILE NATIVE LIST VIEW --}}
    <div class="d-lg-none pb-5 mb-5">
        <ul class="list-group list-group-flush bg-transparent">
            @forelse($users as $user)
            <li class="list-group-item bg-transparent border-0 px-3 py-3 mobile-user-item">
                <div class="card shadow-sm border-0 rounded-4 position-relative h-100">
                     {{-- Status Stripe --}}
                     <div class="position-absolute start-0 top-0 bottom-0 rounded-start-4" style="width: 5px; background-color: {{ $user->is_active ? '#198754' : '#6c757d' }};"></div>
                     
                    <div class="card-body p-3 ps-3">
                        <div class="d-flex align-items-center gap-3">
                            {{-- Avatar --}}
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm flex-shrink-0" 
                                style="width: 50px; height: 50px; background: {{ $user->role == 'admin' ? 'linear-gradient(135deg, #dc3545, #ff6b6b)' : 'linear-gradient(135deg, #0dcaf0, #4dabf7)' }}; font-size: 1.2rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h6>
                                    @if(auth()->user()->role !== 'auditor')
                                        <div class="dropdown" onclick="event.stopPropagation();">
                                            <button class="btn btn-link text-muted p-0 no-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                                                <li><h6 class="dropdown-header">Actions</h6></li>
                                                @if(in_array(auth()->user()->role, ['admin', 'manager']))
                                                    <li>
                                                        <a class="dropdown-item py-2" href="{{ route('users.edit', $user->id) }}">
                                                            <i class="fas fa-edit me-2 text-warning"></i> Edit User
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button class="dropdown-item py-2" type="button" onclick="this.closest('form').submit()">
                                                                <i class="fas {{ $user->is_active ? 'fa-ban text-secondary' : 'fa-check text-success' }} me-2"></i> {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item py-2 text-danger" onclick="confirmDeleteMobile({{ $user->id }})">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </button>
                                                        <form id="delete-form-mobile-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: none;">
                                                            @csrf @method('DELETE')
                                                        </form>
                                                    </li>
                                                @else
                                                    <li><span class="dropdown-item disabled small text-muted">View Only</span></li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate mb-1">{{ $user->email }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge rounded-pill {{ $user->role == 'admin' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-dark' }} border-0 px-2 py-1" style="font-weight: 500;">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                    <i class="fas fa-users-slash fa-4x mb-3 text-secondary"></i>
                    <p class="h6 text-muted">No users found</p>
                </div>
            </li>
            @endforelse
        </ul>
        
        @if(method_exists($users, 'links'))
        <div class="px-3 pb-3">
             {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- FLOATING ACTION BUTTON (Mobile Only) --}}
    @if(auth()->user()->role !== 'auditor')
    <a href="{{ route('users.create') }}" class="btn btn-primary rounded-circle shadow-lg d-lg-none d-flex align-items-center justify-content-center position-fixed" 
       style="bottom: 80px; right: 20px; width: 60px; height: 60px; z-index: 1030;">
        <i class="fas fa-plus fa-lg"></i>
    </a>
    @endif

</div>

<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: 'Delete User?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }

    function confirmDeleteMobile(userId) {
        Swal.fire({
            title: 'Delete User?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-mobile-' + userId).submit();
            }
        });
    }
</script>
@endsection