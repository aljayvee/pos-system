<div class="modal fade" id="categoryProductsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-box-open me-2"></i> Products in <span id="modalCategoryName" class="text-warning"></span>
                    <span id="modalProductCount" class="badge bg-white text-primary rounded-pill ms-2 small" style="display:none; font-size: 0.7rem;">0</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div id="productListLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted fw-bold small">Loading products...</p>
                </div>
                
                <div id="productListContent" class="list-group list-group-flush" style="display: none;">
                    {{-- JS Injected Content --}}
                </div>
                
                <div id="productListEmpty" class="text-center py-5" style="display: none;">
                    <i class="fas fa-box-open fa-3x text-muted opacity-25 mb-3"></i>
                    <h6 class="text-muted fw-bold">No products found</h6>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-light rounded-pill fw-bold border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openProductListModal(id, name) {
        document.getElementById('modalCategoryName').innerText = name;
        const modal = new bootstrap.Modal(document.getElementById('categoryProductsModal'));
        modal.show();

        // UI Reset
        const loader = document.getElementById('productListLoader');
        const content = document.getElementById('productListContent');
        const empty = document.getElementById('productListEmpty');
        const countBadge = document.getElementById('modalProductCount');
        
        loader.style.display = 'block';
        content.style.display = 'none';
        content.innerHTML = '';
        empty.style.display = 'none';
        countBadge.style.display = 'none';

        // Fetch
        fetch(`/admin/categories/${id}/products`)
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                if (data.length > 0) {
                    content.style.display = 'block';
                    // Update Count
                    const countBadge = document.getElementById('modalProductCount');
                    countBadge.innerText = data.length;
                    countBadge.style.display = 'inline-block';

                    data.forEach(p => {
                        const imgElem = p.image 
                            ? `<img src="${p.image}" class="rounded-3 shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">`
                            : `<div class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-secondary small" style="width: 50px; height: 50px;"><i class="fas fa-image"></i></div>`;
                        
                        const html = `
                            <div class="list-group-item p-3 d-flex align-items-center gap-3 border-bottom-0 hover-bg-light">
                                ${imgElem}
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-0">${p.name}</h6>
                                    <span class="text-primary fw-bold small">₱${p.price}</span>
                                </div>
                            </div>
                        `;
                        content.innerHTML += html;
                    });
                } else {
                    empty.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
                loader.style.display = 'none';
                empty.innerHTML = `<p class="text-danger fw-bold">Failed to load data.</p>`;
                empty.style.display = 'block';
            });
    }
</script>
