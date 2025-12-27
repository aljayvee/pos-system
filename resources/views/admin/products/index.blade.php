@extends('admin.layout')

@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-2 py-3 px-md-4 py-md-4">
    
    {{-- HEADER --}}
    <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark m-0 tracking-tight">Product Inventory</h3>
            <p class="text-muted small m-0">Manage stock, prices, and categories.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('inventory.edit')
            <button class="btn btn-white border shadow-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-2 text-secondary"></i>Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary shadow-lg rounded-pill px-4 fw-bold">
                <i class="fas fa-plus-circle me-2"></i>Add New Product
            </a>
            @endcan
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative h-100">
                <div class="card-body p-3 position-relative z-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-column">
                            <span class="text-muted small text-uppercase fw-bold ls-1">Total Items</span>
                            <h2 class="mb-0 fw-bold text-dark mt-1">{{ \App\Models\Product::count() }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                            <i class="fas fa-box fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative h-100">
                <div class="card-body p-3 position-relative z-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-column">
                            <span class="text-muted small text-uppercase fw-bold ls-1">Low Stock</span>
                            <h2 class="mb-0 fw-bold text-danger mt-1">{{ \App\Models\Product::whereRaw('stock <= reorder_point')->count() }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4 d-none d-lg-block d-none d-lg-block">
        <div class="card-body p-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border-0">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Search product..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Filters --}}
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select border-0 bg-light shadow-sm rounded-pill" style="cursor: pointer;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="filter" class="form-select border-0 bg-light shadow-sm rounded-pill" style="cursor: pointer;">
                        <option value="">All Status</option>
                        <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('filter') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 flex-fill fw-bold shadow-sm">Filter</button>
                    
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning rounded-pill flex-fill fw-bold shadow-sm">Active</a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-outline-secondary rounded-pill flex-fill shadow-sm" title="View Archived">
                            <i class="fas fa-archive"></i>
                        </a>
                    @endif

                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Reset">
                            <i class="fas fa-undo text-secondary"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP TABLE --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden d-none d-lg-block mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Product Name</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Category</th>
                        <th class="text-end py-3 text-secondary text-uppercase small fw-bold">Price</th>
                        <th class="text-center py-3 text-secondary text-uppercase small fw-bold">Stock</th>
                        <th class="text-center py-3 text-secondary text-uppercase small fw-bold">Status</th>
                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    {{ substr($product->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    @if($product->sku) <span class="badge bg-light text-secondary border fw-normal">{{ $product->sku }}</span> @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-white border text-secondary fw-normal px-3 py-2 rounded-pill shadow-sm">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                        </td>
                        <td class="text-end fw-bold text-dark">₱{{ number_format($product->price, 2) }}</td>
                        <td class="text-center">
                            <span class="{{ $product->stock <= $product->reorder_point ? 'text-danger fw-bold' : 'text-dark fw-bold' }}">{{ $product->stock }}</span>
                            <small class="text-muted d-block x-small">{{ $product->unit }}</small>
                        </td>
                        <td class="text-center">
                            @if($product->stock == 0) <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Out of Stock</span>
                            @elseif($product->stock <= $product->reorder_point) <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark-emphasis rounded-pill px-3">Low Stock</span>
                            @else <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @include('admin.products.partials.actions', ['product' => $product])
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MOBILE NATIVE VIEW --}}
    <div class="d-lg-none">
        
        {{-- Sticky Search Header --}}
        <div class="sticky-top bg-white border-bottom shadow-sm p-3 mb-3" style="top: 0; z-index: 1020;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h5 class="fw-bold text-dark m-0 tracking-tight flex-grow-1">Products</h5>
                @can('inventory.edit')
                <button class="btn btn-light rounded-circle border shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal" style="width: 40px; height: 40px;">
                    <i class="fas fa-file-import text-secondary"></i>
                </button>
                @endcan
            </div>
            
            <form action="{{ route('products.index') }}" method="GET">
                <div class="input-group shadow-sm rounded-4 overflow-hidden bg-light border">
                    <span class="input-group-text bg-transparent border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-transparent shadow-none" placeholder="Search..." value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('products.index') }}" class="btn btn-link text-muted text-decoration-none"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </form>
            
            {{-- Quick Filters (Horizontal Scroll) --}}
            <div class="d-flex gap-2 mt-2 overflow-auto pb-1" style="scrollbar-width: none;">
                {{-- All Items --}}
                <a href="{{ route('products.index') }}" class="btn btn-sm rounded-pill text-nowrap {{ !request('filter') && !request('category') ? 'btn-dark text-white' : 'btn-light border text-secondary' }}">
                    All Items
                </a>

                {{-- Out of Stock (New) --}}
                <a href="{{ request('filter') == 'out_of_stock' ? route('products.index', request()->except('filter')) : route('products.index', array_merge(request()->all(), ['filter' => 'out_of_stock'])) }}" 
                   class="btn btn-sm rounded-pill text-nowrap {{ request('filter') == 'out_of_stock' ? 'btn-danger text-white' : 'btn-light border text-secondary' }}">
                    Out of Stock
                </a>

                <a href="{{ request('filter') == 'low_stock' ? route('products.index', request()->except('filter')) : route('products.index', array_merge(request()->all(), ['filter' => 'low_stock'])) }}" 
                   class="btn btn-sm rounded-pill text-nowrap {{ request('filter') == 'low_stock' ? 'btn-warning text-dark' : 'btn-light border text-secondary' }}">
                    Low Stock
                </a>

                {{-- Mobile Archived Button --}}
                @if(request('archived'))
                    <a href="{{ route('products.index') }}" class="btn btn-sm rounded-pill text-nowrap btn-warning text-dark">
                        Active Items
                    </a>
                @else
                    <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-sm rounded-pill text-nowrap btn-light border text-secondary">
                        <i class="fas fa-archive me-1"></i>Archived
                    </a>
                @endif
                
                {{-- Clear Separation Line --}}
                <div class="mx-2 bg-dark bg-opacity-25 rounded-pill" style="width: 2px; min-height: 20px;"></div>
                
                @foreach($categories->take(10) as $cat)
                    <a href="{{ request('category') == $cat->id ? route('products.index', request()->except('category')) : route('products.index', array_merge(request()->except('page'), ['category' => $cat->id])) }}" 
                       class="btn btn-sm rounded-pill text-nowrap {{ request('category') == $cat->id ? 'btn-primary text-white' : 'btn-light border text-secondary' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Product List --}}
        <div class="list-group list-group-flush mx-0">
            @forelse($products as $product)
            <div class="list-group-item p-3 border-bottom d-flex align-items-center gap-3" onclick="openProductActionSheet({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ route('products.edit', $product->id) }}', '{{ route('products.destroy', $product->id) }}', {{ $barcodeEnabled ? 1 : 0 }}, '{{ route('products.barcode', $product->id) }}')" style="cursor: pointer;">
                
                {{-- Avatar --}}
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 50px; height: 50px;">
                    {{ substr($product->name, 0, 1) }}
                </div>
                
                {{-- Details --}}
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark text-truncate">{{ $product->name }}</span>
                        <span class="fw-bold text-primary">₱{{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small text-truncate" style="max-width: 150px;">{{ $product->category->name ?? 'Uncategorized' }}</span>
                        
                        {{-- Stock Badge --}}
                        @if($product->stock == 0)
                            <span class="badge bg-danger-subtle text-danger rounded-pill x-small">Out of Stock</span>
                        @elseif($product->stock <= $product->reorder_point)
                            <span class="badge bg-warning-subtle text-warning text-dark-emphasis rounded-pill x-small">Low: {{ $product->stock }}</span>
                        @else
                            <span class="text-secondary x-small">{{ $product->stock }} {{ $product->unit }}</span>
                        @endif
                    </div>
                </div>
                
                <i class="fas fa-chevron-right text-muted opacity-25"></i>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted opacity-25 mb-3"></i>
                <p class="text-muted fw-bold">No products found</p>
                @if(request()->anyFilled(['search', 'category', 'filter']))
                    <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill border px-4">Clear Filters</a>
                @endif
            </div>
            @endforelse
        </div>
        
        {{-- FAB --}}
        @can('inventory.edit')
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center" 
           style="bottom: 90px; right: 20px; width: 60px; height: 60px; z-index: 1030;">
            <i class="fas fa-plus fa-lg text-white"></i>
        </a>
        @endcan

    </div>

    {{-- BOTTOM SHEET ACTION MODAL --}}
    <div class="modal fade" id="productActionSheet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down" style="align-items: flex-end; margin-bottom: 0;">
            <div class="modal-content rounded-top-5 border-0 shadow-lg bg-light">
                <div class="modal-header border-0 pb-2 pt-4 justify-content-center">
                    <div style="width: 50px; height: 5px; background-color: #e0e0e0; border-radius: 10px;"></div>
                </div>
                <div class="modal-body px-3 pb-4 pt-0">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark m-0" id="actionSheetTitle">Product Name</h5>
                        <p class="text-muted small m-0">Select an action</p>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @can('inventory.edit')
                        <div class="bg-white rounded-4 overflow-hidden shadow-sm">
                            <a href="#" id="actionEdit" class="btn btn-white w-100 p-3 text-start fw-bold d-flex align-items-center border-bottom text-dark">
                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-pen"></i>
                                </div>
                                <span>Edit Details</span>
                                <i class="fas fa-chevron-right ms-auto text-muted opacity-25"></i>
                            </a>
                            
                            <a href="#" id="actionBarcode" class="btn btn-white w-100 p-3 text-start fw-bold d-flex align-items-center border-bottom text-dark">
                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-barcode"></i>
                                </div>
                                <span>Print Barcode</span>
                                <i class="fas fa-chevron-right ms-auto text-muted opacity-25"></i>
                            </a>

                            <form id="actionDeleteForm" action="#" method="POST" onsubmit="return confirm('Delete this product?');" class="w-100">
                                @csrf @method('DELETE')
                                <button class="btn btn-white w-100 p-3 text-start fw-bold d-flex align-items-center text-danger border-0">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                    <span>Delete Product</span>
                                </button>
                            </form>
                        </div>
                        @endcan
                        
                        <button type="button" class="btn btn-white fw-bold py-3 rounded-4 shadow-sm text-dark mt-2" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openProductActionSheet(id, name, editUrl, deleteUrl, hasBarcode, barcodeUrl) {
            document.getElementById('actionSheetTitle').innerText = name;
            document.getElementById('actionEdit').href = editUrl;
            document.getElementById('actionDeleteForm').action = deleteUrl;
            
            const barcodeBtn = document.getElementById('actionBarcode');
            if (hasBarcode) {
                barcodeBtn.href = barcodeUrl;
                barcodeBtn.style.display = 'flex';
            } else {
                barcodeBtn.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('productActionSheet')).show();
        }
    </script>
    
    <style>
        .rounded-top-5 { border-top-left-radius: 2rem !important; border-top-right-radius: 2rem !important; }
        .x-small { font-size: 0.75rem; }
    </style>

    @if($products->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $products->links() }}</div>
    @endif

    @include('admin.products.partials.import-modal')

</div>
@endsection
