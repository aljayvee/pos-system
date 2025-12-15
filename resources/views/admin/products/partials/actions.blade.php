@php
    $isMobile = $mobile ?? false;
    $btnClass = $isMobile ? 'btn btn-outline-secondary flex-fill' : 'btn btn-sm btn-outline-primary';
    $barcodeEnabled = \App\Models\Setting::where('key', 'enable_barcode')->value('value') ?? '0';
@endphp

@if(request('archived'))
    <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success flex-fill" title="Restore">
            <i class="fas fa-trash-restore"></i> <span class="{{ $isMobile ? '' : 'd-none' }}">Restore</span>
        </button>
    </form>
    <form action="{{ route('products.force_delete', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete?');">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-danger flex-fill" title="Delete Forever">
            <i class="fas fa-times"></i>
        </button>
    </form>
@else
    @if($barcodeEnabled == '1' && $product->sku)
        <a href="{{ route('products.barcode', $product->id) }}" target="_blank" class="{{ $isMobile ? 'btn btn-dark flex-fill' : 'btn btn-sm btn-dark' }}" title="Barcode">
            <i class="fas fa-barcode"></i>
        </a>
    @endif

    <a href="{{ route('products.edit', $product->id) }}" class="{{ $isMobile ? 'btn btn-primary flex-fill' : 'btn btn-sm btn-primary' }}" title="Edit">
        <i class="fas fa-edit"></i> <span class="{{ $isMobile ? '' : 'd-none' }}">Edit</span>
    </a>

    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive product?');">
        @csrf @method('DELETE')
        <button class="{{ $isMobile ? 'btn btn-outline-danger flex-fill' : 'btn btn-sm btn-outline-danger' }}" title="Archive">
            <i class="fas fa-archive"></i>
        </button>
    </form>
@endif