@extends('admin.layout')

@section('content')
    <div class="container-fluid px-2 py-3 px-md-4 py-md-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Batch Add Products</h3>
                <p class="text-muted small m-0">Add multiple items at once.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        {{-- Form --}}
        <form action="{{ route('products.batch_store') }}" method="POST" id="batchForm">
            @csrf
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="batchTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="min-width: 200px;">Product Name <span class="text-danger">*</span>
                                </th>
                                <th style="min-width: 150px;">Category <span class="text-danger">*</span></th>
                                <th style="min-width: 120px;">Price <span class="text-danger">*</span></th>
                                <th style="min-width: 120px;">Cost</th>
                                <th style="min-width: 100px;">Stock</th>
                                <th style="min-width: 100px;">Reorder Pt</th>
                                <th style="min-width: 100px;">Unit <span class="text-danger">*</span></th>
                                <th style="min-width: 150px;">SKU (Opt)</th>
                                <th style="min-width: 130px;">Expiration</th>
                                @if(config('safety_flag_features.bir_tax_compliance'))
                                    <th style="min-width: 120px;">Tax Type</th>
                                @endif
                                <th class="text-end pe-4" style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- Rows will be injected here --}}
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-light border-top text-center">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold" onclick="addRow()">
                        <i class="fas fa-plus me-2"></i> Add Row
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg" id="btnSave">
                    <i class="fas fa-save me-2"></i> Save All Products
                </button>
            </div>
        </form>
    </div>

    <script>
        // Data from Backend
        const categories = @json($categories);
        const birEnabled = {{ config('safety_flag_features.bir_tax_compliance') ? 'true' : 'false' }};
        let rowCount = 0;

        function addRow() {
            const tbody = document.getElementById('tableBody');
            const index = rowCount++;

            let catOptions = '<option value="" disabled selected>Select...</option>';
            categories.forEach(c => {
                catOptions += `<option value="${c.id}">${c.name}</option>`;
            });

            const tr = document.createElement('tr');

            // Construct Tax Cell or Hidden Input
            let taxCell = '';
            if (birEnabled) {
                taxCell = `
                        <td>
                            <select name="products[${index}][tax_type]" class="form-select border-0 bg-light">
                                <option value="vatable" selected>Vatable</option>
                                <option value="vat_exempt">Exempt</option>
                                <option value="zero_rated">Zero Rate</option>
                            </select>
                        </td>
                    `;
            } else {
                taxCell = `<input type="hidden" name="products[${index}][tax_type]" value="vatable">`;
            }

            tr.innerHTML = `
                    <td class="ps-4">
                        <input type="text" name="products[${index}][name]" class="form-control border-0 bg-light fw-bold" placeholder="Item Name" required>
                    </td>
                    <td>
                        <select name="products[${index}][category_id]" class="form-select border-0 bg-light" required>
                            ${catOptions}
                        </select>
                    </td>
                    <td>
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text bg-light border-0">₱</span>
                            <input type="number" step="0.01" name="products[${index}][price]" class="form-control border-0 bg-light" placeholder="0.00" required>
                        </div>
                    </td>
                    <td>
                         <div class="input-group flex-nowrap">
                            <span class="input-group-text bg-light border-0">₱</span>
                            <input type="number" step="0.01" name="products[${index}][cost]" class="form-control border-0 bg-light" placeholder="0.00">
                        </div>
                    </td>
                    <td>
                        <input type="number" name="products[${index}][stock]" class="form-control border-0 bg-light" value="0" min="0">
                    </td>
                    <td>
                        <input type="number" name="products[${index}][reorder_point]" class="form-control border-0 bg-light" value="0" min="0">
                    </td>
                     <td>
                        <input type="text" name="products[${index}][unit]" class="form-control border-0 bg-light" list="unitOptions" placeholder="Unit" required>
                    </td>
                    <td>
                        <input type="text" name="products[${index}][sku]" class="form-control border-0 bg-light" placeholder="SKU">
                    </td>
                    <td>
                        <input type="date" name="products[${index}][expiration_date]" class="form-control border-0 bg-light">
                    </td>
                    ${taxCell}
                    <td class="text-end pe-4">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 delete-row-btn" onclick="removeRow(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;
            tbody.appendChild(tr);
        }

        function removeRow(btn) {
            const rows = document.getElementById('tableBody').querySelectorAll('tr');
            if (rows.length > 1) {
                btn.closest('tr').remove();
            } else {
                // Clear inputs if it's the last row
                const inputs = btn.closest('tr').querySelectorAll('input');
                inputs.forEach(i => i.value = '');
            }
        }

        // Add initial rows
        document.addEventListener('DOMContentLoaded', () => {
            addRow();
            addRow();
            addRow();
        });

        // Datalist for Units
        const datalist = document.createElement('datalist');
        datalist.id = 'unitOptions';
        datalist.innerHTML = `
                <option value="Pc">
                <option value="Pack">
                <option value="Box">
                <option value="Bottle">
                <option value="Can">
                <option value="Kg">
                <option value="L">
                <option value="Set">
            `;
        document.body.appendChild(datalist);

    </script>
@endsection