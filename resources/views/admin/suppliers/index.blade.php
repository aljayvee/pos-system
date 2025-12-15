@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-truck text-primary me-2"></i>Supplier Management</h1>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus me-1"></i> Add Supplier
        </button>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH BAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search supplier name or contact..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW: TABLE --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Supplier Name</th>
                            <th class="py-3">Contact Info</th>
                            <th class="py-3">History</th>
                            <th class="text-center pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $supplier->name }}</td>
                            <td class="text-muted">{{ $supplier->contact_info ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $supplier->purchases_count }} Transactions
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info text-white me-1 shadow-sm" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-primary me-1 shadow-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editSupplierModal-{{ $supplier->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this supplier?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger shadow-sm" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }} title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @include('admin.suppliers.partials.edit-modal', ['supplier' => $supplier])
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No suppliers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($suppliers->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    {{-- MOBILE VIEW: CARDS --}}
    <div class="d-lg-none">
        <div class="row g-3">
            @forelse($suppliers as $supplier)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $supplier->name }}</h5>
                            <span class="badge bg-light text-secondary border">{{ $supplier->purchases_count }} Trx</span>
                        </div>
                        
                        <div class="text-muted small mb-3">
                            <i class="fas fa-address-card me-2 text-secondary"></i> {{ $supplier->contact_info ?? 'No contact info' }}
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info text-white flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn btn-sm btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#editSupplierModal-{{ $supplier->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline flex-fill" onsubmit="return confirm('Delete supplier?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.suppliers.partials.edit-modal', ['supplier' => $supplier])
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-truck fa-3x mb-3 opacity-25"></i>
                <p>No suppliers found.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">
             {{ $suppliers->links() }}
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Supplier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Information</label>
                        <input type="text" name="contact_info" class="form-control" placeholder="Phone, Email, or Address">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Supplier</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection