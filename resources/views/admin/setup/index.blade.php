<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup | VERAPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/premium-ui.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .step-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .step-card.selected {
            border-color: #4f46e5;
            background-color: #eff6ff;
        }

        .step-card.selected .icon-box {
            background-color: #4f46e5 !important;
            color: white !important;
        }

        .icon-box {
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 p-4">

    <div class="card border-0 shadow-xl rounded-4 overflow-hidden w-100" style="max-width: 800px;">
        <div class="card-header bg-white border-0 p-5 pb-0 text-center">
            <h2 class="fw-bold text-dark mb-2">Welcome to VERAPOS</h2>
            <p class="text-muted">Let's set up your store environment. This will only take a minute.</p>
        </div>

        <div class="card-body p-5">
            <form action="{{ route('admin.setup.store') }}" method="POST" id="setupForm">
                @csrf
                <input type="hidden" name="system_mode" id="system_mode">

                {{-- STEP 1: MODE SELECTION --}}
                <div id="step-1">
                    <h6 class="text-uppercase fw-bold text-secondary small mb-4 text-center ls-1">Select Operation Mode
                    </h6>
                    <div class="row g-4 justify-content-center">
                        {{-- Independent Store --}}
                        <div class="col-md-6">
                            <div class="card h-100 p-4 step-card text-center" onclick="selectMode('single', this)">
                                <div class="icon-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                    style="width: 64px; height: 64px; font-size: 1.5rem;">
                                    <i class="fas fa-store"></i>
                                </div>
                                <h5 class="fw-bold">Independent Store</h5>
                                <p class="small text-muted mb-0">Single location. Simple management. Perfect for
                                    standalone businesses.</p>
                            </div>
                        </div>

                        {{-- Multi Store --}}
                        <div class="col-md-6">
                            <div class="card h-100 p-4 step-card text-center" onclick="selectMode('multi', this)">
                                <div class="icon-box bg-light text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                    style="width: 64px; height: 64px; font-size: 1.5rem;">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <h5 class="fw-bold">Multi-Store Chain</h5>
                                <p class="small text-muted mb-0">Multiple branches. Centralized inventory and reporting.
                                    Scalable growth.</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-5">
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg"
                            onclick="nextStep()" disabled id="btn-step-1">
                            Continue Setup <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: STORE DETAILS --}}
                <div id="step-2" style="display: none;">
                    <div class="d-flex align-items-center mb-4">
                        <button type="button" class="btn btn-light btn-sm rounded-circle me-3" onclick="prevStep()"><i
                                class="fas fa-arrow-left"></i></button>
                        <h5 class="fw-bold m-0">Store Details</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Store Name</label>
                            <input type="text" name="store_name" class="form-control form-control-lg bg-light border-0"
                                placeholder="e.g. My Awesome Store" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Owner Name</label>
                            <input type="text" name="owner_name" class="form-control form-control-lg bg-light border-0"
                                placeholder="Your Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control bg-light border-0"
                                placeholder="Mobile or Landline">
                        </div>

                        <div class="col-12">
                            <hr class="my-2 opacity-50">
                        </div>
                        <h6 class="fw-bold small text-secondary text-uppercase ls-1">Location Address</h6>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Country</label>
                            <select name="country" class="form-select bg-light border-0">
                                <option value="Philippines" selected>Philippines</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Region</label>
                            <select id="setup_region" class="form-select bg-light border-0"
                                onchange="loadCities('setup'); updateHiddenName('setup', 'region')">
                                <option value="">Select Region</option>
                            </select>
                            <input type="hidden" name="region" id="setup_region_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">City / Municipality</label>
                            <select id="setup_city" class="form-select bg-light border-0"
                                onchange="loadBarangays('setup'); updateHiddenName('setup', 'city')" disabled>
                                <option value="">Select City</option>
                            </select>
                            <input type="hidden" name="city" id="setup_city_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Barangay</label>
                            <select name="barangay" id="setup_barangay" class="form-select bg-light border-0" disabled>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Street / Building / Purok</label>
                            <input type="text" name="street" class="form-control bg-light border-0"
                                placeholder="Unit 123, Example Bldg, Main St.">
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow-lg">
                            <i class="fas fa-check-circle me-2"></i> Complete Setup
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- JS LOGIC --}}
    <script>
        // --- WIZARD LOGIC ---
        function selectMode(mode, element) {
            document.getElementById('system_mode').value = mode;
            // Visual Selection
            document.querySelectorAll('.step-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('btn-step-1').disabled = false;
        }

        function nextStep() {
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
            // Start loading regions when entering step 2
            loadRegions('setup');
        }

        function prevStep() {
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-1').style.display = 'block';
        }

        // --- ADDRESS API LOGIC ---
        const API_BASE = 'https://psgc.gitlab.io/api';

        async function loadRegions(prefix) {
            const select = document.getElementById(`${prefix}_region`);
            if (select.options.length > 1) return; // Already loaded

            select.innerHTML = '<option value="">Loading...</option>';
            select.disabled = true;

            try {
                const response = await fetch(`${API_BASE}/regions/`);
                const data = await response.json();
                data.sort((a, b) => a.name.localeCompare(b.name));

                select.innerHTML = '<option value="">Select Region</option>';
                data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.code;
                    option.dataset.name = region.name;
                    option.textContent = `${region.name} (${region.regionName})`;
                    select.appendChild(option);
                });
                select.disabled = false;
            } catch (error) {
                console.error('Error:', error);
                select.innerHTML = '<option value="">Error loading data</option>';
            }
        }

        async function loadCities(prefix) {
            const regionCode = document.getElementById(`${prefix}_region`).value;
            const citySelect = document.getElementById(`${prefix}_city`);
            const barangaySelect = document.getElementById(`${prefix}_barangay`);

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
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;
            } catch (error) {
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
                    option.value = brgy.name;
                    option.textContent = brgy.name;
                    barangaySelect.appendChild(option);
                });
                barangaySelect.disabled = false;
            } catch (error) {
                barangaySelect.innerHTML = '<option value="">Error loading data</option>';
            }
        }

        function updateHiddenName(prefix, type) {
            const select = document.getElementById(`${prefix}_${type}`);
            const hiddenInput = document.getElementById(`${prefix}_${type}_name`);
            if (select.selectedIndex >= 0) {
                const selectedOption = select.options[select.selectedIndex];
                hiddenInput.value = selectedOption.dataset.name || selectedOption.value;
            }
        }
    </script>
</body>

</html>