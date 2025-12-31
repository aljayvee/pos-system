{{-- Quick Category Creation Modal --}}
<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2 text-primary"></i>New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="quickCategoryForm" onsubmit="saveQuickCategory(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Category Name</label>
                        <input type="text" id="quickCategoryName" class="form-control bg-light border-0 py-3 fw-bold rounded-3" placeholder="e.g. Beverages" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" id="btn-save-category" class="btn btn-primary rounded-pill fw-bold py-3 shadow-sm">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    async function saveQuickCategory(event) {
        event.preventDefault();
        
        const nameInput = document.getElementById('quickCategoryName');
        const btn = document.getElementById('btn-save-category');
        const originalText = btn.innerHTML;
        const modalEl = document.getElementById('quickCategoryModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);

        if (!nameInput.value.trim()) return;

        try {
            // Loading State
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating...';

            const response = await fetch("{{ route('categories.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: nameInput.value.trim() })
            });

            if (!response.ok) throw new Error('Failed to create category');

            const data = await response.json();

            if (data.success) {
                // Add to Select Dropdown
                const select = document.getElementById('categorySelect');
                if (select) {
                    const option = new Option(data.category.name, data.category.id, true, true);
                    select.add(option, undefined); // Add to end
                    // Trigger change event if needed
                    select.dispatchEvent(new Event('change'));
                }

                // Show Success & Close
                // Use SweetAlert if available, otherwise native alert or just close
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Category Created',
                        text: data.category.name + ' has been added.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Category created successfully!');
                }

                nameInput.value = ''; // Reset form
                modal.hide();
            }

        } catch (error) {
            console.error(error);
            alert("Error creating category. Please try again.");
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
