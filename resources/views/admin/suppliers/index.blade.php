@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-truck text-primary me-2"></i>Suppliers</h1>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-1"></i> Add Supplier</a>
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

    {{-- SEARCH --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search supplier..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2"><button class="btn btn-dark w-100">Search</button></div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW --}}
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
                            <td><span class="badge bg-light text-secondary border">{{ $supplier->purchases_count }} Transactions</span></td>
                            <td class="text-center pe-4">
                                <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info text-white me-1 shadow-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-primary me-1 shadow-sm"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger shadow-sm" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted">No suppliers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($suppliers->hasPages()) <div class="card-footer bg-white border-top-0 py-3">{{ $suppliers->links() }}</div> @endif
    </div>

    {{-- === MOBILE NATIVE VIEW === --}}
    <div class="d-lg-none">
        @forelse($suppliers as $supplier)
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; border-left: 5px solid #0dcaf0;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold text-dark mb-0">{{ $supplier->name }}</h5>
                    <span class="badge bg-light text-secondary border rounded-pill">{{ $supplier->purchases_count }} Orders</span>
                </div>
                
                <div class="text-muted small mb-3">
                    <i class="fas fa-address-card me-2 text-info"></i> {{ $supplier->contact_info ?? 'No contact info' }}
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-outline-info flex-fill py-2 rounded-3">
                        View
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-primary flex-fill py-2 rounded-3">
                        Edit
                    </a>
                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Delete?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100 py-2 rounded-3" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-truck fa-3x mb-3 opacity-25"></i>
            <p>No suppliers found.</p>
        </div>
        @endforelse
        <div class="mt-4">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection