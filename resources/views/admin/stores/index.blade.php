@extends('admin.layout')

@section('content')
<div class="container-fluid px-0 px-md-4 py-0 py-md-4">
    
    {{-- MOBILE HEADER --}}
    <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm z-3">
        <div class="px-3 py-3 d-flex align-items-center justify-content-between">
            <h4 class="m-0 fw-bold text-dark"><i class="fas fa-store text-primary me-2"></i>Branches</h4>
            <button class="btn btn-primary btn-sm rounded-pill fw-bold px-3 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#createStoreDrawer">
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
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-lg" data-bs-toggle="offcanvas" data-bs-target="#createStoreDrawer">
            <i class="fas fa-plus-circle me-2"></i> Add New Branch
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 m-3 m-lg-0 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                                    onclick="openEditModal({{ $store->id }}, '{{ addslashes($store->name) }}', '{{ addslashes($store->address) }}', '{{ addslashes($store->contact_number) }}', '{{ addslashes($store->street) }}', '{{ addslashes($store->barangay) }}', '{{ addslashes($store->city) }}', '{{ addslashes($store->region) }}', '{{ addslashes($store->country) }}')" 
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
                 style="border-style: dashed; border-color: #cbd5e1; min-height: 250px;"
                 data-bs-toggle="offcanvas" data-bs-target="#createStoreDrawer">
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
                                    <button class="btn btn-link p-0 text-muted" onclick="openEditModal({{ $store->id }}, '{{ addslashes($store->name) }}', '{{ addslashes($store->address) }}', '{{ addslashes($store->contact_number) }}', '{{ addslashes($store->street) }}', '{{ addslashes($store->barangay) }}', '{{ addslashes($store->city) }}', '{{ addslashes($store->region) }}', '{{ addslashes($store->country) }}')">
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

{{-- CREATE STORE DRAWER (OFFCANVAS) --}}
{{-- CREATE STORE DRAWER (OFFCANVAS) --}}
<div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="createStoreDrawer" style="width: 500px;" data-bs-backdrop="false" data-bs-scroll="true">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-building me-2"></i>New Branch</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <form action="{{ route('stores.store') }}" method="POST" class="d-flex flex-column h-100">
            @csrf
            <div class="p-4 flex-grow-1 overflow-auto">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary text-uppercase ls-1">Branch Details</label>
                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="e.g. Downtown Branch" required>
                </div>
                
                <h6 class="fw-bold small text-secondary text-uppercase ls-1 mb-3">Location Address</h6>
                <div class="row g-2 mb-4">
                     <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">Country</label>
                        <select name="country" class="form-select bg-light border-0">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">Region</label>
                        <select id="create_region" class="form-select bg-light border-0" onchange="loadCities('create'); updateHiddenName('create', 'region')">
                            <option value="">Select Region</option>
                        </select>
                        <input type="hidden" name="region" id="create_region_name">
                    </div>
                    <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">City / Municipality</label>
                        <select id="create_city" class="form-select bg-light border-0" onchange="loadBarangays('create'); updateHiddenName('create', 'city')" disabled>
                            <option value="">Select City</option>
                        </select>
                        <input type="hidden" name="city" id="create_city_name">
                    </div>
                    <div class="col-6 col-md-6">
                         <label class="form-label small text-muted">Barangay</label>
                        <select name="barangay" id="create_barangay" class="form-select bg-light border-0" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Street / Building / Purok</label>
                        <input type="text" name="street" class="form-control bg-light border-0" placeholder="Unit 123, Example Bldg, Main St.">
                    </div>
                </div>

                <div class="mb-3">
                     <label class="form-label fw-bold small text-secondary text-uppercase ls-1">Contact Information</label>
                    <input type="text" name="contact_number" class="form-control bg-light border-0" placeholder="Phone Number">
                </div>
            </div>
            <div class="p-4 border-top bg-light">
                 <div class="d-grid gap-2">
                     <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">Create Branch</button>
                     <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold text-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                 </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT STORE DRAWER (OFFCANVAS) --}}
{{-- EDIT STORE DRAWER (OFFCANVAS) --}}
<div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="editStoreDrawer" style="width: 500px;" data-bs-backdrop="false" data-bs-scroll="true">
    <div class="offcanvas-header bg-warning text-dark">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Branch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
         <form id="editStoreForm" method="POST" class="d-flex flex-column h-100">
            @csrf
            @method('PUT')
            <div class="p-4 flex-grow-1 overflow-auto">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary text-uppercase ls-1">Branch Details</label>
                    <input type="text" name="name" id="editName" class="form-control form-control-lg bg-light border-0" required>
                </div>
                
                <h6 class="fw-bold small text-secondary text-uppercase ls-1 mb-3">Location Address</h6>
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">Country</label>
                        <select name="country" id="edit_country" class="form-select bg-light border-0">
                            <option value="Philippines" selected>Philippines</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">Region</label>
                        <select id="edit_region" class="form-select bg-light border-0" onchange="loadCities('edit'); updateHiddenName('edit', 'region')">
                            <option value="">Select Region</option>
                        </select>
                         <input type="hidden" name="region" id="edit_region_name">
                    </div>
                    <div class="col-6 col-md-6">
                        <label class="form-label small text-muted">City / Municipality</label>
                        <select id="edit_city" class="form-select bg-light border-0" onchange="loadBarangays('edit'); updateHiddenName('edit', 'city')" disabled>
                            <option value="">Select City</option>
                        </select>
                        <input type="hidden" name="city" id="edit_city_name">
                    </div>
                    <div class="col-6 col-md-6">
                         <label class="form-label small text-muted">Barangay</label>
                        <select name="barangay" id="edit_barangay" class="form-select bg-light border-0">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted">Street / Building / Purok</label>
                        <input type="text" name="street" id="edit_street" class="form-control bg-light border-0" placeholder="Unit 123, Example Bldg, Main St.">
                    </div>
                </div>

                <div class="mb-3">
                     <label class="form-label fw-bold small text-secondary text-uppercase ls-1">Contact Information</label>
                    <input type="text" name="contact_number" id="editContact" class="form-control bg-light border-0">
                </div>
            </div>
            <div class="p-4 border-top bg-light">
                 <div class="d-grid gap-2">
                     <button type="submit" class="btn btn-warning btn-lg rounded-pill fw-bold shadow-sm">Save Changes</button>
                     <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold text-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                 </div>
            </div>
        </form>
    </div>
</div>

<script>
    const API_BASE = 'https://psgc.gitlab.io/api';

    // Initialize Dropdowns
    document.addEventListener('DOMContentLoaded', () => {
        loadRegions('create');
        loadRegions('edit');
    });

    async function loadRegions(prefix) {
        const select = document.getElementById(`${prefix}_region`);
        select.innerHTML = '<option value="">Loading...</option>';
        select.disabled = true;

        try {
            const response = await fetch(`${API_BASE}/regions/`);
            const data = await response.json();
            
            // Sort by name
            data.sort((a, b) => a.name.localeCompare(b.name));

            select.innerHTML = '<option value="">Select Region</option>';
            data.forEach(region => {
                const option = document.createElement('option');
                option.value = region.code; // Use code for API calls, but we also store name
                option.dataset.name = region.name;
                option.textContent = `${region.name} (${region.regionName})`;
                select.appendChild(option);
            });
            select.disabled = false;
        } catch (error) {
            console.error('Error loading regions:', error);
            select.innerHTML = '<option value="">Error loading data</option>';
        }
    }

    async function loadCities(prefix) {
        const regionCode = document.getElementById(`${prefix}_region`).value;
        const citySelect = document.getElementById(`${prefix}_city`);
        const barangaySelect = document.getElementById(`${prefix}_barangay`);

        // Reset lower levels
        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (!regionCode) return;

        citySelect.innerHTML = '<option value="">Loading...</option>';

        try {
            const response = await fetch(`${API_BASE}/regions/${regionCode}/cities-municipalities/`);
            const data = await response.json();

            data.sort((a, b) => a.name.localeCompare(b.name));

            citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
            data.forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.dataset.name = city.name;
                // Distinguish City vs Municipality if needed, or just show name
                option.textContent = city.name; 
                citySelect.appendChild(option);
            });
            citySelect.disabled = false;
        } catch (error) {
            console.error('Error loading cities:', error);
            citySelect.innerHTML = '<option value="">Error loading data</option>';
        }
    }

    async function loadBarangays(prefix) {
        const cityCode = document.getElementById(`${prefix}_city`).value;
        const barangaySelect = document.getElementById(`${prefix}_barangay`);

        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;

        if (!cityCode) return;

        barangaySelect.innerHTML = '<option value="">Loading...</option>';

        try {
            const response = await fetch(`${API_BASE}/cities-municipalities/${cityCode}/barangays/`);
            const data = await response.json();

            data.sort((a, b) => a.name.localeCompare(b.name));

            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            data.forEach(brgy => {
                const option = document.createElement('option');
                option.value = brgy.name; // User stores the name directly usually, or we can use code. 
                // Since the Model stores 'string', and there's no 'barangay_code' column, 
                // we should probably store the NAME. 
                // However, the value of the 'region' and 'city' selects above are CODES to facilitate chaining.
                // We need to inject a hidden input to store the actual NAME for region/city upon submission.
                option.textContent = brgy.name;
                barangaySelect.appendChild(option);
            });
            barangaySelect.disabled = false;
        } catch (error) {
            console.error('Error loading barangays:', error);
            barangaySelect.innerHTML = '<option value="">Error loading data</option>';
        }
    }

    // Since we are submitting a form, we need the NAMES of Region/City, not the CODES.
    // We'll intercept the form submission or hidden fields.
    // Better strategy: Add hidden inputs for region_name and city_name.
    // Or simpler: Set the value of the option to the NAME, but store the CODE in data attribute.
    // But value is needed for the 'onchange' to get the code.
    // Solution: When the dropdown changes, update a hidden input field with the text name.
    
    function updateHiddenName(prefix, type) {
        const select = document.getElementById(`${prefix}_${type}`);
        const hiddenInput = document.getElementById(`${prefix}_${type}_name`);
        if (select.selectedIndex >= 0) {
            const selectedOption = select.options[select.selectedIndex];
            hiddenInput.value = selectedOption.dataset.name || selectedOption.value; // Fallback
        }
    }

    // Wait! The previous implementation didn't rely on hidden fields, it just submitted 'region', 'city'.
    // If I change value to code, the DB stores the code "130000000". The user sees "NCR".
    // When viewing again, we need to show "NCR".
    // If the DB stores "NCR" (string), we need to submit "NCR".
    // So the <select> name should NOT be 'region' directly if the value is the code.
    // I will rename the selects to `region_code`, `city_code` and have hidden inputs named `region`, `city` 
    // that get updated on change.

    function openEditModal(id, name, address, contact, street, barangay, city, region, country) {
        document.getElementById('editStoreForm').action = `/admin/stores/${id}`;
        document.getElementById('editName').value = name;
        document.getElementById('editContact').value = contact !== 'null' ? contact : '';
        document.getElementById('edit_street').value = street !== 'null' ? street : '';
        document.getElementById('edit_country').value = country && country !== 'null' ? country : 'Philippines';

        // Set the saved string names into the hidden fields (we will create these)
        // logic below requires these fields to exist.

        // Trigger the cascading load to pre-fill
        // This is hard because we only have the NAME 'NCR', not the code '133900000'.
        // We have to Find the code from the name. This requires fetching all regions first (already done on load).
        
        preSelectLocation('edit', region, city, barangay);
        
        new bootstrap.Offcanvas(document.getElementById('editStoreDrawer')).show();
    }

    async function preSelectLocation(prefix, regionName, cityName, barangayName) {
        // 1. Select Region
        const regionSelect = document.getElementById(`${prefix}_region`);
        
        // Wait for regions to load if empty (rare but possible if clicked fast)
        if (regionSelect.options.length <= 1) {
            await new Promise(r => setTimeout(r, 500)); // Simple wait
        }

        // Find option with data-name == regionName
        let regionCode = '';
        for (let i = 0; i < regionSelect.options.length; i++) {
            if (regionSelect.options[i].dataset.name === regionName) {
                regionSelect.selectedIndex = i;
                regionCode = regionSelect.options[i].value;
                break;
            }
        }
        
        if (regionCode) {
            // Trigger City Load
            await loadCities(prefix); // This is async, waits for fetch.
            
            // 2. Select City
            const citySelect = document.getElementById(`${prefix}_city`);
            let cityCode = '';
             for (let i = 0; i < citySelect.options.length; i++) {
                if (citySelect.options[i].dataset.name === cityName) {
                    citySelect.selectedIndex = i;
                    cityCode = citySelect.options[i].value;
                    break;
                }
            }

            if (cityCode) {
                // Trigger Barangay Load
               await loadBarangays(prefix);

               // 3. Select Barangay
               const brgySelect = document.getElementById(`${prefix}_barangay`);
               brgySelect.value = barangayName;
            }
        }
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