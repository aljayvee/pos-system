<script setup>
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth.user);
const stats = computed(() => page.props.stats);
const settings = computed(() => page.props.settings);

// Data passed from Controller
defineProps({
    categories: Array
});

const form = useForm({
    name: ''
});

const editForm = useForm({
    name: ''
});
const editId = ref(null);

const addCategory = () => {
    form.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};

const openEditModal = (cat) => {
    editForm.name = cat.name;
    editId.value = cat.id;
    new window.bootstrap.Modal(document.getElementById('editCategoryModal')).show();
};

const submitEditForm = () => {
    editForm.put(`/admin/categories/${editId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            const modal = window.bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
            if(modal) modal.hide();
        }
    });
};

const deleteCategory = (id) => {
    if (confirm('Delete this category?')) {
        useForm({}).delete(`/admin/categories/${id}`, {
            preserveScroll: true
        });
    }
};

const handleCategoryClick = (id, name, count) => {
    if (window.innerWidth < 992) {
        document.getElementById('actionSheetTitle').innerText = name;
        
        let viewBtn = document.getElementById('actionViewProducts');
        viewBtn.onclick = () => {
            const myModal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryActionSheet'));
            myModal.hide();
            // Note: Product View Logic is normally a separate modal, omitting for simplicity in migration or implementing basic redirect
            window.location.href = `/admin/categories/${id}/products`; 
        };

        let editBtn = document.getElementById('actionEdit');
        editBtn.onclick = () => {
            const myModal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryActionSheet'));
            myModal.hide();
            openEditModal({id, name});
        };

        let deleteBtn = document.getElementById('actionDeleteBtn');
        deleteBtn.onclick = () => {
            const myModal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryActionSheet'));
            myModal.hide();
            deleteCategory(id);
        };

        new window.bootstrap.Modal(document.getElementById('categoryActionSheet')).show();
    }
};

</script>

<template>
    <Head title="Category Management" />

    <admin-layout 
        :user-name="auth.name" 
        :user-role="auth.role"
        :user-permissions="auth.effective_permissions"
        :user-photo="auth.profile_photo_path"
        page-title="Category Management" 
        csrf-token="" 
        :out-of-stock="stats.outOfStock"
        :low-stock="stats.lowStock"
        :enable-register-logs="settings.enable_register_logs"
        :enable-bir-compliance="settings.enable_bir_compliance"
        :user-id="auth.id" 
        :system-mode="settings.system_mode">
        
        <div class="container-fluid px-2 py-3 px-md-4 py-md-4">

            <!-- MOBILE HEADER -->
            <div class="d-lg-none sticky-top bg-white border-bottom shadow-sm px-3 py-3 d-flex align-items-center justify-content-between z-3 mb-3" style="top: 0;">
                <div style="width: 40px;"></div> 
                <h6 class="m-0 fw-bold text-dark">Categories</h6>
                <div style="width: 40px;"></div>
            </div>

            <!-- DESKTOP HEADER -->
            <div class="d-none d-lg-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-dark m-0 tracking-tight">Category Management</h3>
                    <p class="text-muted small m-0">Organize products into logical groups.</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill shadow-sm">
                        Total: {{ categories.length }}
                    </span>
                </div>
            </div>

            <div class="row g-4 mb-5 pb-5 mb-lg-0 pb-lg-0">
                <!-- Main Content -->
                <div class="col-lg-8">

                    <!-- DESKTOP: Add New Category Card -->
                    <div v-if="auth.role !== 'auditor'" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden d-none d-lg-block">
                        <div class="card-header bg-primary text-white py-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                        </div>
                        <div class="card-body p-4">
                            <form @submit.prevent="addCategory">
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="position-relative flex-fill">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                                            <i class="fas fa-tag"></i>
                                        </span>
                                        <input type="text" v-model="form.name" class="form-control ps-5 bg-light border-0 shadow-sm" placeholder="e.g. Beverages, Snacks, Electronics" required>
                                    </div>
                                    <button type="submit" :disabled="form.processing" class="btn btn-primary btn-lg px-4 rounded-3 shadow-lg fw-bold">
                                        {{ form.processing ? 'Adding...' : 'Add' }}
                                    </button>
                                </div>
                                <div v-if="form.errors.name" class="text-danger small mt-2 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ form.errors.name }}</div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white border-bottom border-light p-4 d-none d-lg-block">
                            <h6 class="fw-bold text-dark mb-0">Existing Categories</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div v-for="cat in categories" :key="cat.id" class="list-group-item p-3 d-flex align-items-center justify-content-between hover-bg-light transition-all" @click="handleCategoryClick(cat.id, cat.name, cat.products_count)" style="cursor: pointer;">

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                            <span class="fw-bold fs-5">{{ cat.name.substring(0, 1) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block fs-6">{{ cat.name }}</span>
                                            <span :class="{'bg-success text-success': cat.products_count > 0, 'bg-danger text-danger': cat.products_count === 0}" class="badge bg-opacity-10 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="fas" :class="cat.products_count > 0 ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                                {{ cat.products_count > 0 ? 'Products exist' : 'No products exist' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Desktop Actions -->
                                    <div v-if="auth.role !== 'auditor'" class="d-none d-lg-flex gap-2" @click.stop>
                                        <button type="button" class="btn btn-light text-primary btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px;" @click="openEditModal(cat)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteCategory(cat.id)" class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" style="width: 36px; height: 36px;" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>

                                    <!-- Mobile Arrow -->
                                    <div v-if="auth.role !== 'auditor'" class="d-lg-none text-muted opacity-25">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                                
                                <div v-if="categories.length === 0" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-folder-open fa-3x text-muted opacity-25"></i>
                                    </div>
                                    <h6 class="text-muted fw-bold">No categories found</h6>
                                    <p class="small text-muted">Create your first category above.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary bg-gradient text-white overflow-hidden mb-4">
                        <div class="card-body p-4 position-relative">
                            <i class="fas fa-lightbulb fa-5x position-absolute top-50 end-0 translate-middle-y text-white opacity-25 me-n3"></i>
                            <h5 class="fw-bold mb-2">Did you know?</h5>
                            <p class="opacity-75 mb-0 small lh-lg">
                                You can organize products more effectively by keeping category names simple and distinct.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAB -->
            <div v-if="auth.role !== 'auditor'" class="position-fixed d-lg-none" style="bottom: 90px; right: 20px; z-index: 1030;">
                <button type="button" @click="() => new window.bootstrap.Modal(document.getElementById('createCategoryModal')).show()" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-plus fa-lg text-white"></i>
                </button>
            </div>

        </div>

        <!-- CREATE MODAL -->
        <div class="modal fade modal-bottom-sheet" id="createCategoryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="d-lg-none"><div class="sheet-handle"></div></div>
                    <div class="modal-header border-0 pb-0 d-none d-lg-flex">
                        <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="addCategory">
                        <div class="modal-body p-4 pt-0 pt-lg-4">
                            <div class="text-center mb-4 d-lg-none">
                                <h5 class="fw-bold text-dark m-0">New Category</h5>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-secondary d-none d-lg-block">Category Name</label>
                                <input type="text" v-model="form.name" class="form-control form-control-lg bg-light border-0 py-3 fw-bold" placeholder="Category Name" required>
                                <div v-if="form.errors.name" class="text-danger small mt-2 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ form.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="form.processing" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg">
                                {{ form.processing ? 'Creating...' : 'Create Category' }}
                            </button>
                            <button type="button" class="btn mobile-cancel-btn mt-3 d-lg-none shadow-sm" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div class="modal fade modal-bottom-sheet" id="editCategoryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="d-lg-none"><div class="sheet-handle"></div></div>
                    <div class="modal-header bg-warning text-dark border-0 d-none d-lg-flex">
                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="submitEditForm">
                        <div class="modal-body p-4 pt-0 pt-lg-4">
                            <div class="text-center mb-4 d-lg-none">
                                <h5 class="fw-bold text-dark m-0">Edit Category</h5>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase text-secondary d-none d-lg-block">Category Name</label>
                                <input type="text" v-model="editForm.name" class="form-control form-control-lg bg-light border-0 py-3 fw-bold" required>
                                <div v-if="editForm.errors.name" class="text-danger small mt-2 fw-bold"><i class="fas fa-exclamation-circle me-1"></i>{{ editForm.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="editForm.processing" class="btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-lg text-dark">
                                {{ editForm.processing ? 'Updating...' : 'Update Category' }}
                            </button>
                            <button type="button" class="btn mobile-cancel-btn mt-3 d-lg-none shadow-sm" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ACTION SHEET (NATIVE STYLE) -->
        <div class="modal fade modal-bottom-sheet" id="categoryActionSheet" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-transparent shadow-none backdrop-blur-0">
                    <div class="bg-surface px-3 pb-4 pt-2 rounded-top-5 mt-auto bg-white border">
                        <div class="sheet-handle"></div>

                        <div class="text-center mb-4">
                            <h5 class="fw-bold text-dark m-0" id="actionSheetTitle">Category</h5>
                            <p class="text-muted small m-0">Select an action</p>
                        </div>

                        <div class="mobile-action-group shadow-sm">
                            <button type="button" id="actionViewProducts" class="mobile-action-btn w-100 text-start border-0 bg-transparent p-3 d-flex align-items-center">
                                <i class="fas fa-box text-success me-3"></i>
                                <span>View Products</span>
                            </button>
                            <button type="button" id="actionEdit" class="mobile-action-btn w-100 text-start border-0 bg-transparent p-3 d-flex align-items-center">
                                <i class="fas fa-pen text-primary me-3"></i>
                                <span>Edit Name</span>
                            </button>
                            <button type="button" id="actionDeleteBtn" class="mobile-action-btn w-100 text-start border-0 bg-transparent p-3 d-flex align-items-center text-danger">
                                <i class="fas fa-trash-alt me-3"></i>
                                <span>Delete Category</span>
                            </button>
                        </div>

                        <button type="button" class="btn btn-light w-100 shadow-sm py-3 mt-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </admin-layout>
</template>

<style scoped>
.hover-bg-light:hover { background-color: #f8f9fa !important; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>
