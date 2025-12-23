@php
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

<div class="btn-group shadow-sm" role="group">
    @if(request('archived'))
        @can('inventory.edit')
        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-success text-white" title="Restore Product" data-bs-toggle="tooltip">
                <i class="fas fa-trash-restore"></i>
            </button>
        </form>
        <form action="{{ route('products.force_delete', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('This will permanently delete the product. Cannot be undone.');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger text-white rounded-end" title="Delete Permanently" data-bs-toggle="tooltip">
                <i class="fas fa-times"></i>
            </button>
        </form>
        @endcan
    @else
        @if($barcodeEnabled == '1' && $product->sku)
            <a href="{{ route('products.barcode', $product->id) }}" target="_blank" class="btn btn-sm btn-light border text-dark hover-bg-light" title="Print Barcode">
                <i class="fas fa-barcode"></i>
            </a>
        @endif

        @can('inventory.edit')
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-light border text-primary hover-bg-light" title="Edit Product">
            <i class="fas fa-edit"></i>
        </a>

        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this product? It will be hidden from the main list.');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-light border text-danger hover-bg-light rounded-end" title="Archive Product">
                <i class="fas fa-archive"></i>
            </button>
        </form>
        @endcan
    @endif
</div>