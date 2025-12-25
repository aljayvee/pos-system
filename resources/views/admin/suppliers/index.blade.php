@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-truck text-primary me-2"></i>Suppliers</h6>
            @if(auth()->user()->role !== 'auditor')
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                <i class="fas fa-plus fa-sm"></i>
            </a>
            @endif
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="fw-bold text-dark mb-1">
            <i class="fas fa-truck text-primary me-2"></i>Suppliers
        </h4>
        @if(auth()->user()->role !== 'auditor')
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary shadow-sm rounded-pill fw-bold px-4">
            <i class="fas fa-plus me-1"></i> Add Supplier
        </a>
        @endif
    </div>

    <div class="px-3 px-md-0 pt-3 pt-md-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }} 
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
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
                                        @if(auth()->user()->role !== 'auditor')
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-light border text-dark px-3"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-light border text-danger px-3" 
                                                onclick="if(confirm('Delete {{ $supplier->name }}?')) document.getElementById('del-{{ $supplier->id }}').submit()"
                                                {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
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
        <div class="d-lg-none menu-grid">
            <div class="list-group list-group-flush">
                @forelse($suppliers as $supplier)
                <div class="list-group-item p-3 border-bottom-0 hover-bg-light" 
                     data-bs-toggle="modal" data-bs-target="#supplierActionSheet-{{ $supplier->id }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">{{ $supplier->name }}</div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-hashtag me-1 opacity-50"></i> {{ $supplier->purchases_count }} Transactions
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-25"></i>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-truck fa-3x mb-3 opacity-25 text-light-gray"></i>
                    <p>No suppliers found.</p>
                </div>
                @endforelse
            </div>
            <div class="mt-4 d-flex justify-content-center">{{ $suppliers->links() }}</div>
        </div>
    </div>

    {{-- MOBILE ACTION SHEETS (Outside Filter) --}}
    @foreach($suppliers as $supplier)
    <div class="modal fade" id="supplierActionSheet-{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable fixed-bottom m-0" style="max-width: 100%;">
            <div class="modal-content rounded-top-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 justify-content-center">
                    <div class="bg-secondary bg-opacity-25 rounded-pill" style="width: 40px; height: 5px;"></div>
                </div>
                <div class="modal-body pt-4 pb-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-truck fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $supplier->name }}</h5>
                        <p class="text-muted small mb-0">{{ $supplier->contact_info ?? 'No contact info' }}</p>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-light shadow-sm p-3 rounded-4 d-flex align-items-center justify-content-center gap-2 fw-bold text-primary">
                            <i class="fas fa-eye fa-lg"></i> View Details
                        </a>
                        
                        @if(auth()->user()->role !== 'auditor')
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-light shadow-sm p-3 rounded-4 d-flex align-items-center justify-content-center gap-2 fw-bold text-dark">
                            <i class="fas fa-edit fa-lg"></i> Edit Supplier
                        </a>
                        
                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Do you really want to delete this supplier?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-light shadow-sm p-3 rounded-4 w-100 d-flex align-items-center justify-content-center gap-2 fw-bold text-danger" {{ $supplier->purchases_count > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash fa-lg"></i> Delete Supplier
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    
</div>
@endsection