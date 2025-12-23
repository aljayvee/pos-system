@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="fw-bold text-dark mb-1">
            <i class="fas fa-truck text-primary me-2"></i>Suppliers
        </h4>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary shadow-sm rounded-pill fw-bold px-4">
            <i class="fas fa-plus me-1"></i> Add Supplier
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-3">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 py-2" 
                               placeholder="Search supplier name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-dark w-100 rounded-pill fw-bold py-2">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Supplier List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Supplier Name</th>
                            <th class="py-3">Contact Info</th>
                            <th class="py-3">Transaction History</th>
                            <th class="text-center pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $supplier->name }}</td>
                            <td class="text-muted">{{ $supplier->contact_info ?? 'N/A' }}</td>
                            <td><span class="badge bg-light text-secondary border rounded-pill px-3">{{ $supplier->purchases_count }} Transactions</span></td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm rounded-pill">
                                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-light border text-primary fw-bold px-3">View</a>
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-light border text-dark px-3"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-light border text-danger px-3" 
                                            onclick="if(confirm('Delete {{ $supplier->name }}?')) document.getElementById('del-{{ $supplier->id }}').submit()"
                                            {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="del-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted">No suppliers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($suppliers->hasPages()) 
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $suppliers->links() }}
        </div> 
        @endif
    </div>

    {{-- === MOBILE NATIVE VIEW === --}}
    <div class="d-lg-none">
        @forelse($suppliers as $supplier)
        <div class="card shadow-sm border-0 mb-3 rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-dark mb-0">{{ $supplier->name }}</h5>
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $supplier->purchases_count }} Orders</span>
                </div>
                
                <div class="text-muted small mb-3 bg-light rounded-3 p-2">
                    <i class="fas fa-address-card me-2 text-secondary"></i> {{ $supplier->contact_info ?? 'No contact info' }}
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-light border text-primary fw-bold flex-fill py-2 rounded-pill shadow-sm">
                        View
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning text-dark fw-bold flex-fill py-2 rounded-pill shadow-sm">
                        Edit
                    </a>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100 py-2 rounded-pill shadow-sm fw-bold border-0 bg-danger-subtle text-danger" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-truck fa-3x mb-3 opacity-25 text-light-gray"></i>
            <p>No suppliers found.</p>
        </div>
        @endforelse
        <div class="mt-4 d-flex justify-content-center">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection