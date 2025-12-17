@extends('admin.layout')

@section('content')
<style>
    :root {
        --primary-soft: #e0e7ff;
        --primary-dark: #4f46e5;
    }
    
    .branch-card {
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        transition: all 0.3s ease;
        background: white;
        height: 100%;
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .branch-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05);
        border-color: var(--primary-soft);
    }
    
    .branch-card.active-branch {
        border: 2px solid var(--primary-dark);
        background: #fdfdff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
    }

    .branch-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }
    
    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block; margin-right: 6px;
    }
</style>

<div class="container-fluid px-1 px-md-4 py-1">
    
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-5">
        <div>
            <h3 class="fw-bold text-dark m-0"><i class="fas fa-store-alt text-primary me-2"></i>Store Branches</h3>
            <p class="text-muted small m-0">Manage locations and switch your active inventory context.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createStoreModal">
            <i class="fas fa-plus me-2"></i> Add New Branch
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Store Grid --}}
    <div class="row g-4">
        @foreach($stores as $store)
        @php 
            $isActiveContext = session('active_store_id', 1) == $store->id;
            $isMain = $store->id == 1;
        @endphp
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="branch-card p-4 {{ $isActiveContext ? 'active-branch' : 'shadow-sm' }}">
                
                {{-- Card Header --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="branch-icon {{ $isActiveContext ? 'bg-primary text-white' : 'bg-light text-secondary' }}">
                        <i class="fas fa-store"></i>
                    </div>
                    @if($isActiveContext)
                        <span class="badge bg-primary rounded-pill fw-normal px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i> Current
                        </span>
                    @elseif($isMain)
                        <span class="badge bg-dark rounded-pill fw-normal px-3 py-2">Main HQ</span>
                    @endif
                </div>

                {{-- Content --}}
                <h5 class="fw-bold text-dark mb-1">{{ $store->name }}</h5>
                <div class="text-muted small mb-3">Branch ID: #{{ $store->id }}</div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2 text-secondary small">
                        <i class="fas fa-map-marker-alt fa-fw me-2 opacity-50"></i>
                        <span class="text-truncate">{{ $store->address ?? 'No address set' }}</span>
                    </div>
                    <div class="d-flex align-items-center text-secondary small">
                        <i class="fas fa-phone fa-fw me-2 opacity-50"></i>
                        <span>{{ $store->contact_number ?? 'No contact' }}</span>
                    </div>
                </div>

                {{-- Footer / Actions --}}
                <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top border-light">
                    <div class="small fw-bold">
                        @if($store->is_active)
                            <span class="text-success"><span class="status-dot bg-success"></span>Active</span>
                        @else
                            <span class="text-danger"><span class="status-dot bg-danger"></span>Inactive</span>
                        @endif
                    </div>

                    @if(!$isActiveContext)
                        <a href="{{ route('stores.switch', $store->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                            Switch To <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    @else
                        <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3" disabled>
                            Selected
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        {{-- "Add New" Placeholder Card (Optional visual cue) --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="branch-card border-dashed p-4 d-flex align-items-center justify-content-center bg-light" 
                 style="border-style: dashed; cursor: pointer; min-height: 260px;"
                 data-bs-toggle="modal" data-bs-target="#createStoreModal">
                <div class="text-center text-muted">
                    <div class="mb-3">
                        <i class="fas fa-plus-circle fa-3x opacity-25"></i>
                    </div>
                    <h6 class="fw-bold">Open New Branch</h6>
                    <small>Click to configure</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Modal (Polished) --}}
<div class="modal fade" id="createStoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 ps-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-building me-2 text-primary"></i>New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stores.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Create a separate inventory context for a new location.</p>
                    
                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control rounded-3" id="storeName" placeholder="Branch Name" required>
                        <label for="storeName">Branch Name <span class="text-danger">*</span></label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="text" name="address" class="form-control rounded-3" id="storeAddress" placeholder="Address">
                        <label for="storeAddress">Location / Address</label>
                    </div>

                    <div class="form-floating">
                        <input type="text" name="contact_number" class="form-control rounded-3" id="storeContact" placeholder="Contact">
                        <label for="storeContact">Contact Number</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pe-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light text-muted fw-bold rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Create Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection