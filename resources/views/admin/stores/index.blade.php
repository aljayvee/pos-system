@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-store-alt text-primary"></i> Store Branches</h2>
            <p class="text-muted">Manage multiple locations and switch inventory contexts.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStoreModal">
            <i class="fas fa-plus me-1"></i> Add New Branch
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Store Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $store)
                        <tr class="{{ session('active_store_id') == $store->id ? 'table-primary' : '' }}">
                            <td class="fw-bold">#{{ $store->id }}</td>
                            <td>
                                <span class="fw-bold">{{ $store->name }}</span>
                                @if($store->id == 1) <span class="badge bg-secondary ms-1">Main</span> @endif
                                @if(session('active_store_id', 1) == $store->id) 
                                    <span class="badge bg-success ms-1">Active Context</span> 
                                @endif
                            </td>
                            <td>{{ $store->address ?? '-' }}</td>
                            <td>{{ $store->contact_number ?? '-' }}</td>
                            <td class="text-center">
                                @if($store->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(session('active_store_id', 1) != $store->id)
                                    <a href="{{ route('stores.switch', $store->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-exchange-alt me-1"></i> Switch To
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-primary" disabled>Current</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Store Modal --}}
<div class="modal fade" id="createStoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stores.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Downtown Branch" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Street, City">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="0912...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection