@extends('admin.layout')

@php
    // Fetch Setting for Barcode Feature
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@section('content')
<div class="container-fluid px-4">
    
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1><i class="fas fa-box-open text-primary"></i> Product Management</h1>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv"></i> Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- SEARCH & FILTER TOOLBAR --}}
    <div class="card bg-light border-0 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
                
                {{-- Search Input --}}
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Search name, barcode/SKU..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="col-md-3">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quick Filter --}}
                <div class="col-md-2">
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Status --</option>
                        <option value="low" {{ request('filter') == 'low' ? 'selected' : '' }}>Low Stock Only</option>
                    </select>
                </div>

                {{-- Buttons (Filter & Archive Toggle) --}}
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark flex-grow-1">Filter</button>
                    
                    @if(request('archived'))
                        <a href="{{ route('products.index') }}" class="btn btn-warning flex-grow-1">
                            <i class="fas fa-box-open"></i> Active
                        </a>
                    @else
                        <a href="{{ route('products.index', ['archived' => 1]) }}" class="btn btn-secondary flex-grow-1" title="View Deleted">
                            <i class="fas fa-trash-alt"></i> Trash
                        </a>
                    @endif

                    @if(request()->anyFilled(['search', 'category', 'filter']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- VISUAL INDICATOR FOR ARCHIVED ITEMS --}}
    @if(request('archived'))
        <div class="alert alert-warning border-start border-warning border-4">
            <i class="fas fa-trash-alt me-2"></i> 
            <strong>Archived Items</strong> — These products are hidden from the POS and Reports. Restore them to make them active again.
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                        <tr>
                            {{-- Helper to keep current filters while sorting --}}
                            @php
                                $queryParams = request()->except(['sort', 'direction', 'page']);
                                function sortLink($field, $label, $params) {
                                    $direction = (request('sort') == $field && request('direction') == 'asc') ? 'desc' : 'asc';
                                    $icon = '';
                                    if (request('sort') == $field) {
                                        $icon = (request('direction') == 'asc') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
                                    }
                                    $url = route('products.index', array_merge($params, ['sort' => $field, 'direction' => $direction]));
                                    return "<a href='{$url}' class='text-decoration-none text-dark fw-bold'>{$label} {$icon}</a>";
                                }
                            @endphp

                            <th>{!! sortLink('name', 'Product Name', $queryParams) !!}</th>
                            <th>{!! sortLink('category', 'Category', $queryParams) !!}</th>
                            <th class="text-end">{!! sortLink('price', 'Price', $queryParams) !!}</th>
                            <th class="text-center">{!! sortLink('stock', 'Stock', $queryParams) !!}</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            <small class="text-muted">{{ ucfirst($product->unit) }}</small>
                        </td>
                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td class="fw-bold {{ $product->stock <= $product->reorder_point ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock }}
                        </td>
                        <td>
                            @if($product->stock == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->stock <= $product->reorder_point)
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            @else
                                <span class="badge bg-success">Good</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(request('archived'))
                                {{-- ARCHIVED MODE: Show Restore & Force Delete --}}
                                <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Restore Product">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                </form>

                                <form action="{{ route('products.force_delete', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete Permanently">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @else
                                {{-- ACTIVE MODE: Show Barcode, Edit, & Archive --}}
                                
                                {{-- 1. BARCODE BUTTON (Condition: Feature ON + Product has SKU) --}}
                                @if($barcodeEnabled == '1' && $product->sku)
                                    <a href="{{ route('products.barcode', $product->id) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-dark me-1" 
                                       title="Print Barcode">
                                        <i class="fas fa-barcode"></i>
                                    </a>
                                @endif

                                {{-- 2. EDIT BUTTON (Links to separate Edit page) --}}
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- 3. ARCHIVE BUTTON --}}
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this product?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Archive">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                            No products found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    </div>

    {{-- IMPORT MODAL --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Products via CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small">
                            Format: <strong>Name, Category, Price, Stock, Barcode/SKU</strong><br>
                            Example: <em>"Coke 1.5L", "Drinks", 75, 100, "12345678"</em>
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" required accept=".csv">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload & Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection