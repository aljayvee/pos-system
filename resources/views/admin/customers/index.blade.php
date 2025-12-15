@extends('admin.layout')

@section('content')
<div class="container-fluid px-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-4 mb-4 gap-2">
        <h1 class="h2 mb-0 text-gray-800"><i class="fas fa-users text-primary me-2"></i>Customer Management</h1>
        <!--<button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-user-plus me-1"></i> Add Customer
        </button>-->
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH BAR --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Search customer name..." value="{{ request('search') }}">
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
                            <th class="ps-4 py-3">Name</th>
                            <th class="py-3">Contact</th>
                            <th class="py-3">Address</th>
                            <th class="text-center py-3">Points</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">{{ $customer->name }}</td>
                            <td class="text-muted">{{ $customer->contact ?? '-' }}</td>
                            <td class="text-muted text-truncate" style="max-width: 200px;">{{ $customer->address ?? '-' }}</td>
                            <td class="text-center">
                                @if($customer->points > 0)
                                    <span class="badge bg-warning-subtle text-warning text-dark-emphasis border border-warning px-3">{{ $customer->points }} pts</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-info text-white me-1 shadow-sm" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-warning shadow-sm me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCustomerModal-{{ $customer->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger shadow-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @include('admin.customers.partials.edit-modal', ['customer' => $customer])
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    {{-- MOBILE VIEW: CARDS --}}
    <div class="d-lg-none">
        <div class="row g-3">
            @forelse($customers as $customer)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $customer->name }}</h5>
                            @if($customer->points > 0)
                                <span class="badge bg-warning text-dark">{{ $customer->points }} pts</span>
                            @endif
                        </div>
                        
                        <div class="small text-muted mb-3">
                            <div class="mb-1"><i class="fas fa-phone-alt me-2 text-secondary w-25px"></i> {{ $customer->contact ?? 'No Contact' }}</div>
                            <div><i class="fas fa-map-marker-alt me-2 text-secondary w-25px"></i> {{ $customer->address ?? 'No Address' }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-info text-white flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn btn-sm btn-warning flex-fill" data-bs-toggle="modal" data-bs-target="#editCustomerModal-{{ $customer->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline flex-fill" onsubmit="return confirm('Delete customer?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.customers.partials.edit-modal', ['customer' => $customer])
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                <p>No customers found.</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4">
             {{ $customers->links() }}
        </div>
    </div>
</div>

{{-- ADD CUSTOMER MODAL --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Full Name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                            <input type="text" name="contact" class="form-control" placeholder="09xxxxxxxxx">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street, Barangay, City..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection