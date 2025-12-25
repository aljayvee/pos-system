@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h4 class="m-0 fw-bold text-dark"><i class="fas fa-store text-primary me-2"></i>Branches</h4>
            <button class="btn btn-primary btn-sm rounded-pill fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createStoreModal">
                <i class="fas fa-plus me-1"></i> Add
            </button>
        </div>
    </div>

    {{-- DESKTOP HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3 mt-4">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Store Management</h3>
            <p class="text-muted small m-0">Manage branches and switch context.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg" data-bs-toggle="modal" data-bs-target="#createStoreModal">
            <i class="fas fa-plus-circle me-2"></i> Add New Branch
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 m-3 m-lg-0 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    </@if>

    {{-- DESKTOP CARD VIEW --}}
    <div class="row g-4 d-none d-lg-flex">
        @foreach($stores as $store)
        @php 
            $isActiveContext = session('active_store_id', 1) == $store->id;
            $isMain = $store->id == 1;
        @endphp
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-0 h-100 {{ $isActiveContext ? 'shadow-lg border-primary' : 'shadow-sm' }} rounded-4 overflow-hidden position-relative transition-all hover-translate-up" 
                 style="{{ $isActiveContext ? 'border: 2px solid #4f46e5;' : '' }}">
                
                {{-- Active Indicator --}}
                @if($isActiveContext)
                    <div class="position-absolute top-0 start-0 w-100 bg-primary opacity-10" style="height: 100%;"></div>
                @endif

                <div class="card-body p-4 position-relative z-1">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 {{ $isActiveContext ? 'bg-primary text-white' : 'bg-light text-secondary' }}" 
                             style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="fas fa-store fa-lg"></i>
                        </div>
                        <div class="d-flex gap-1">
                            @if(!$isMain)
                            <button class="btn btn-sm btn-light text-secondary rounded-circle shadow-sm" style="width: 32px; height: 32px;" 
                                    onclick="openEditModal({{ $store->id }}, '{{ addslashes($store->name) }}', '{{ addslashes($store->address) }}', '{{ addslashes($store->contact_number) }}')" 
                                    title="Edit Details">
                                <i class="fas fa-pen-to-square x-small"></i>
                            </button>
                            @endif
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $store->name }}</h5>
                    <div class="mb-3">
                        @if($isActiveContext)
                            <span class="badge bg-primary rounded-pill small">Active Context</span>
                        @elseif($isMain)
                             <span class="badge bg-dark rounded-pill small">Main HQ</span>
                        @else
                            <span class="badge bg-light text-muted border rounded-pill small">Branch #{{ $store->id }}</span>
                        @endif
                    </div>

                    <div class="text-secondary small mb-1 d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-2 opacity-50" style="width: 16px;"></i>
                        <span class="text-truncate">{{ $store->address ?? 'No address' }}</span>
                    </div>
                    <div class="text-secondary small d-flex align-items-center">
                        <i class="fas fa-phone me-2 opacity-50" style="width: 16px;"></i>
                        <span>{{ $store->contact_number ?? 'No contact' }}</span>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="card-footer bg-white border-top border-light p-3 position-relative z-1">
                    @if(!$isActiveContext)
                        <a href="{{ route('stores.switch', $store->id) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold shadow-sm">
                            Switch Context
                        </a>
                    @else
                         <button class="btn btn-light w-100 rounded-pill fw-bold text-primary" disabled>
                            <i class="fas fa-check me-1"></i> Current View
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        {{-- Add New Card (Clickable) --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card border-2 border-dashed h-100 shadow-none rounded-4 d-flex align-items-center justify-content-center bg-light cursor-pointer hover-bg-white transition-all"
                 style="border-style: dashed; border-color: #cbd5e1; min-height: 250px;"
                 data-bs-toggle="modal" data-bs-target="#createStoreModal">
                <div class="text-center p-4">
                    <div class="mb-3 text-muted opacity-50">
                        <i class="fas fa-plus-circle fa-3x"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Open New Branch</h6>
                    <small class="text-muted">Click to configure details</small>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE NATIVE LIST VIEW --}}
    <div class="d-lg-none pb-5 mb-5">
        <ul class="list-group list-group-flush">
            @foreach($stores as $store)
            @php 
                $isActiveContext = session('active_store_id', 1) == $store->id;
                $isMain = $store->id == 1;
            @endphp
            <li class="list-group-item bg-transparent border-0 px-3 py-2">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden {{ $isActiveContext ? 'border border-primary' : '' }}">
                    @if($isActiveContext)
                    <div class="position-absolute start-0 top-0 bottom-0 bg-primary" style="width: 5px;"></div>
                    @endif
                    <div class="card-body p-3 ps-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 {{ $isActiveContext ? 'bg-primary text-white' : 'bg-light text-secondary' }}" style="width: 48px; height: 48px;">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $store->name }}</h6>
                                    @if(!$isMain)
                                    <button class="btn btn-link p-0 text-muted" onclick="openEditModal({{ $store->id }}, '{{ addslashes($store->name) }}', '{{ addslashes($store->address) }}', '{{ addslashes($store->contact_number) }}')">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate">{{ $store->address ?? 'No address' }}</div>
                                <div class="d-flex mt-2">
                                    @if($isActiveContext)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Active</span>
                                    @else
                                        <a href="{{ route('stores.switch', $store->id) }}" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3 fw-bold small">Switch</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

</div>

{{-- CREATE STORE MODAL --}}
<div class="modal fade" id="createStoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-building me-2"></i>New Branch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stores.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-light border-0" placeholder="e.g. Downtown Branch" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Address</label>
                        <input type="text" name="address" class="form-control bg-light border-0" placeholder="Location">
                    </div>
                    <div class="mb-3">
                         <label class="form-label fw-bold small text-secondary">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control bg-light border-0" placeholder="Phone">
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-between p-4 bg-light rounded-bottom-4">
                     <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                     <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm">Create Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT STORE MODAL --}}
<div class="modal fade" id="editStoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
             <form id="editStoreForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Address</label>
                        <input type="text" name="address" id="editAddress" class="form-control bg-light border-0">
                    </div>
                    <div class="mb-3">
                         <label class="form-label fw-bold small text-secondary">Contact Number</label>
                        <input type="text" name="contact_number" id="editContact" class="form-control bg-light border-0">
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-between p-4 bg-light rounded-bottom-4">
                     <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                     <button type="button" onclick="document.getElementById('editStoreForm').submit()" class="btn btn-warning rounded-pill fw-bold px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name, address, contact) {
        document.getElementById('editStoreForm').action = `/admin/stores/${id}`;
        document.getElementById('editName').value = name;
        document.getElementById('editAddress').value = address !== 'null' ? address : '';
        document.getElementById('editContact').value = contact !== 'null' ? contact : '';
        new bootstrap.Modal(document.getElementById('editStoreModal')).show();
    }
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    .hover-translate-up:hover { transform: translateY(-5px); }
    .transition-all { transition: all 0.3s ease; }
    .x-small { font-size: 0.75rem; }
    .hover-bg-white:hover { background-color: #fff !important; border-color: #94a3b8 !important; }
</style>
@endsection