@extends('admin.layout')

@section('content')
    <div class="container-fluid p-0">

        {{-- PAGE HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 px-4 pt-4">
            <div>
                <h2 class="h3 mb-1 text-gray-800">BIR Compliance</h2>
                <p class="text-muted mb-0">Manage tax, accreditation, and receipt settings.</p>
            </div>
            <button class="btn btn-primary shadow-sm" onclick="document.getElementById('birSettingsForm').submit()">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
        </div>

        <div class="row px-4">
            <div class="col-lg-8">
                <form action="{{ route('admin.bir.update') }}" method="POST" id="birSettingsForm">
                    @csrf

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- 1. ACCREDITATION INFO --}}
                    <div class="card shadow-sm mb-4 border-0 rounded-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="m-0 fw-bold text-primary text-uppercase small ls-1">Accreditation Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">TIN (Tax Identification No.)</label>
                                    <input type="text" name="store_tin" class="form-control" placeholder="000-000-000-000"
                                        value="{{ $settings['store_tin'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Business Permit Number</label>
                                    <input type="text" name="business_permit" class="form-control"
                                        placeholder="BP-202X-XXXX" value="{{ $settings['business_permit'] ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info py-2 small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        These details will appear on the <strong>Official Sales Invoice</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MACHINE REGISTRATION --}}
                    <div class="card shadow-sm mb-4 border-0 rounded-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="m-0 fw-bold text-primary text-uppercase small ls-1">Machine Registration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">PTU Number</label>
                                    <input type="text" name="ptu_number" class="form-control" placeholder="FP0123..."
                                        value="{{ $settings['ptu_number'] ?? '' }}">
                                    <div class="form-text small">Permit To Use</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">MIN</label>
                                    <input type="text" name="min_number" class="form-control" placeholder="MIN123456789"
                                        value="{{ $settings['min_number'] ?? '' }}">
                                    <div class="form-text small">Machine ID Date</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Serial Number</label>
                                    <input type="text" name="serial_number" class="form-control" placeholder="SN-123..."
                                        value="{{ $settings['serial_number'] ?? '' }}">
                                    <div class="form-text small">Software Serial</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. TAX CONFIGURATION --}}
                    <div class="card shadow-sm mb-4 border-0 rounded-4">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <h6 class="m-0 fw-bold text-primary text-uppercase small ls-1">Tax Calculation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Tax Type</label>
                                    <select name="tax_type" class="form-select">
                                        <option value="inclusive" {{ ($settings['tax_type'] ?? '') == 'inclusive' ? 'selected' : '' }}>VAT Inclusive</option>
                                        <option value="exclusive" {{ ($settings['tax_type'] ?? '') == 'exclusive' ? 'selected' : '' }}>VAT Exclusive</option>
                                        <option value="non_vat" {{ ($settings['tax_type'] ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">VAT Rate (%)</label>
                                    <input type="number" name="tax_rate" class="form-control"
                                        value="{{ $settings['tax_rate'] ?? '12' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- SIDEBAR INFO / PREVIEW --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mb-4"
                    style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
                    <div class="card-body p-4">
                        <h5 class="fw-bold"><i class="fas fa-shield-alt me-2"></i>Status</h5>
                        <p class="mb-0 opacity-75 small">This system is operating in <strong>BIR COMPLIANT MODE</strong>.
                        </p>
                        <hr class="bg-white opacity-25">
                        <ul class="list-unstyled small mb-0 opacity-75">
                            <li class="mb-2"><i class="fas fa-check me-2"></i>Persistent Grand Accumulators</li>
                            <li class="mb-2"><i class="fas fa-check me-2"></i>Sequential Invoice Numbering</li>
                            <li><i class="fas fa-check me-2"></i>Electronic Journal Logging</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection