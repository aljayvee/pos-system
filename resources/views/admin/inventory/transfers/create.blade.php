@extends('admin.layout')

@section('content')
    <div class="container-fluid px-0 px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Transfer Stock</h3>
                <p class="text-muted small m-0">Move inventory between branches.</p>
            </div>
            <a href="{{ route('transfers.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                <i class="fas fa-list me-1"></i> View History
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('transfers.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        {{-- FROM STORE --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Source Branch (From)</label>
                            <select name="from_store_id" class="form-select form-select-lg bg-light border-0">
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (old('from_store_id') == $store->id) ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Where the stock will be taken from.</div>
                        </div>

                        {{-- TO STORE --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Destination Branch (To)</label>
                            <select name="to_store_id" class="form-select form-select-lg bg-light border-0">
                                @foreach($targetStores as $store)
                                    <option value="{{ $store->id }}" {{ (old('to_store_id') == $store->id) ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Where the stock will be sent to.</div>
                        </div>

                        {{-- PRODUCT --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-secondary">Product to Transfer</label>
                            {{-- Simple Select for now, ideally Searchable --}}
                            <input class="form-control form-control-lg bg-light border-0" list="productOptions"
                                name="product_search" placeholder="Type to search product..." autocomplete="off"
                                onchange="updateProductId(this)">
                            <datalist id="productOptions">
                                @foreach($products as $product)
                                    <option data-value="{{ $product->id }}" value="{{ $product->name }} ({{ $product->sku }})">
                                    </option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="product_id" id="hidden_product_id" value="{{ old('product_id') }}">
                            @error('product_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- QUANTITY --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Quantity</label>
                            <input type="number" name="quantity" class="form-control form-control-lg bg-light border-0"
                                min="1" value="{{ old('quantity') }}" required>
                        </div>

                        {{-- NOTES --}}
                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-secondary">Notes (Optional)</label>
                            <input type="text" name="notes" class="form-control form-control-lg bg-light border-0"
                                placeholder="Reason for transfer..." value="{{ old('notes') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('inventory.index') }}"
                            class="btn btn-light rounded-pill px-4 me-2 fw-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg">
                            <i class="fas fa-exchange-alt me-2"></i> Confirm Transfer
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function updateProductId(input) {
            const list = document.getElementById('productOptions');
            const hiddenInput = document.getElementById('hidden_product_id');
            const options = list.options;

            // Find matching option
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input.value) {
                    hiddenInput.value = options[i].getAttribute('data-value');
                    break;
                }
            }
        }
    </script>
@endsection