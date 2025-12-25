@extends('admin.layout')

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-users text-primary me-2"></i>Customer Management
            </h4>
            <p class="text-muted small mb-0">Manage customer profiles, loyalty points, and history.</p>
        </div>
        @if(auth()->user()->role !== 'auditor')
        <button class="btn btn-primary shadow-sm rounded-pill fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus-circle me-2"></i>New Customer
        </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH TOOLBAR --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body p-3">
            <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 py-2" 
                               placeholder="Search by name, phone, or address..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill fw-bold py-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP VIEW --}}
    <div class="card shadow-sm border-0 d-none d-lg-block mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Customer List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4 py-3">Customer Name</th>
                            <th class="py-3">Contact Info</th>
                            <th class="py-3">Address</th>
                            <th class="text-center py-3">Loyalty Points</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; font-weight: bold;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <span class="fw-bold text-dark">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="text-muted">
                                @if($customer->contact)
                                    <i class="fas fa-phone-alt me-1 text-secondary small"></i> {{ $customer->contact }}
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-muted text-truncate" style="max-width: 200px;">
                                {{ $customer->address ?? '-' }}
                            </td>
                            <td class="text-center">
                                @if($customer->points > 0)
                                    <span class="badge bg-warning-subtle text-warning text-dark-emphasis border border-warning px-3 py-1 rounded-pill">
                                        <i class="fas fa-star me-1 text-warning"></i>{{ $customer->points }} pts
                                    </span>
                                @else 
                                    <span class="badge bg-light text-secondary border rounded-pill">0 pts</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-light border-end" title="View Profile">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    @if(auth()->user()->role !== 'auditor')
                                    <button class="btn btn-sm btn-light border-end" data-bs-toggle="modal" data-bs-target="#editCustomerModal-{{ $customer->id }}" title="Edit">
                                        <i class="fas fa-edit text-warning"></i>
                                    </button>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger rounded-end-pill" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @include('admin.customers.partials.edit-modal', ['customer' => $customer])
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages()) 
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $customers->links() }}
        </div> 
        @endif
    </div>

    {{-- === MOBILE NATIVE VIEW === --}}
    <div class="d-lg-none">
        @forelse($customers as $customer)
        <div class="card shadow-sm border-0 mb-3 rounded-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-3">
                    {{-- Avatar --}}
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-0">{{ $customer->name }}</h6>
                        @if($customer->points > 0)
                            <span class="badge bg-warning text-dark rounded-pill small mt-1">
                                <i class="fas fa-star me-1"></i>{{ $customer->points }} pts
                            </span>
                        @endif
                    </div>

                    {{-- Quick Call --}}
                    @if($customer->contact)
                        <a href="tel:{{ $customer->contact }}" class="btn btn-success rounded-circle shadow-sm" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-phone-alt"></i>
                        </a>
                    @endif
                </div>
                
                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex align-items-center text-muted mb-2">
                        <i class="fas fa-mobile-alt me-2 opacity-50" style="width: 20px; text-align: center;"></i> 
                        {{ $customer->contact ?? 'No Contact Info' }}
                    </div>
                    <div class="d-flex align-items-start text-muted">
                        <i class="fas fa-map-marker-alt me-2 opacity-50 mt-1" style="width: 20px; text-align: center;"></i> 
                        <span>{{ $customer->address ?? 'No Address' }}</span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-light w-100 fw-bold border">
                            View
                        </a>
                    </div>
                    @if(auth()->user()->role !== 'auditor')
                    <div class="col-6">
                        <button class="btn btn-warning w-100 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#editCustomerModal-{{ $customer->id }}">
                            Edit
                        </button>
                    </div>
                    <div class="col-6">
                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Delete this customer?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger w-100 fw-bold border-2">Delete</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @include('admin.customers.partials.edit-modal', ['customer' => $customer])
        @empty
        <div class="text-center py-5 text-muted">
            <div class="mb-3">
                <i class="fas fa-users fa-3x text-light-gray opacity-25"></i>
            </div>
            <h6 class="fw-bold text-secondary">No customers found</h6>
        </div>
        @endforelse
        <div class="mt-4 d-flex justify-content-center">{{ $customers->links() }}</div>
    </div>

</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        @if(auth()->user()->role !== 'auditor')
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Full Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Contact Number</label>
                        <input type="text" name="contact" class="form-control bg-light border-0" placeholder="09xxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Address</label>
                        <textarea name="address" class="form-control bg-light border-0" rows="3" placeholder="Residential or Delivery Address"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Save Customer</button>
                </div>
            </div>
        </form>
        @else
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <h5>Access Denied</h5>
                <p>Auditors cannot add new customers.</p>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection