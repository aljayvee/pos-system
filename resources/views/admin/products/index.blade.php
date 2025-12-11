@extends('admin.layout')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Inventory</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New Product
        </a>
    </div>

    @if(session('success')) 
        <div class="alert alert-success">{{ session('success') }}</div> 
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->sku ?? '-' }}</td>
                        <td>{{ $product->name }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $product->category->name ?? 'None' }}</span>
                        </td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->cost ? '₱'.number_format($product->cost, 2) : '-' }}</td>
                        <td>
                            @if($product->stock <= $product->alert_stock)
                                <span class="text-danger fw-bold">{{ $product->stock }} (Low)</span>
                            @else
                                <span class="text-success">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <h5 class="text-muted">No products available</h5>
                            <a href="{{ route('products.create') }}" class="btn btn-sm btn-outline-primary">Add your first product</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection