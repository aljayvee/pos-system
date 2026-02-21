<template>
    <div>
        <!-- ALERT (Mobile & Desktop) -->
        <div class="mb-4" v-if="outOfStockItems > 0">
            <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 rounded-4 p-4 glass-panel position-relative overflow-hidden" role="alert">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-danger opacity-10 pe-none"></div>
                <div class="rounded-circle bg-white text-danger p-3 me-3 shadow-sm position-relative z-1">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div class="position-relative z-1">
                    <h5 class="alert-heading fw-bold mb-1">Attention Needed</h5>
                    <p class="mb-0">
                        You have <span class="fw-bold text-decoration-underline">{{ outOfStockItems }} items</span> out of stock.
                        <a v-if="canRestock" :href="restockUrl" class="btn btn-sm btn-light text-danger fw-bold ms-2 rounded-pill px-3 shadow-sm hover-scale">Restock Now</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- DESKTOP GRID (Hidden on Mobile) -->
        <div class="row g-4 mb-5 d-none d-md-flex">
            <!-- ROW 1: CASH FLOW METRICS (TODAY) -->
            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Realized Revenue" :value="'₱' + stats.realizedSalesToday" subtitle="Cash + Digital + Collections" icon="fas fa-coins" color="primary"></stats-card>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Debt Collections" :value="'₱' + stats.debtCollectionsToday" subtitle="Collected Today" icon="fas fa-hand-holding-usd" color="info"></stats-card>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Cash in Drawer" :value="'₱' + stats.estCashInDrawer" subtitle="Expected Cash on Hand" icon="fas fa-wallet" color="success"></stats-card>
            </div>

            <!-- ROW 2: PERFORMANCE & STATUS -->
            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Profit (Today)" :value="'₱' + stats.profitToday" subtitle="Net Income" icon="fas fa-chart-line" color="success"></stats-card>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Monthly Sales (Accrual)" :value="'₱' + stats.salesMonth" subtitle="Includes Unpaid Credits" icon="fas fa-calendar-check" color="primary"></stats-card>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <stats-card title="Total Collectibles" :value="'₱' + stats.totalCredits" subtitle="Unpaid Customer Debts" icon="fas fa-file-invoice-dollar" color="warning"></stats-card>
            </div>
        </div>

        <!-- MOBILE ACCORDION (Visible on Mobile Only) -->
        <div class="d-md-none mb-5">
            <div class="d-flex flex-column gap-3">
                <!-- DRAWER 1: CASH FLOW -->
                <div class="border-0 rounded-4 shadow-sm overflow-hidden glass-panel mb-0">
                    <button class="w-100 bg-transparent py-4 px-4 shadow-none border-0 text-start d-flex align-items-center" type="button" @click="toggleDrawer('drawerCash')">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="fas fa-wallet text-primary" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-0">Cash Position</h6>
                            <small class="text-muted">In Drawer: <span class="fw-bold text-dark">₱{{ stats.estCashInDrawer }}</span></small>
                        </div>
                        <i class="fas fa-chevron-down text-muted transition-transform" :class="{'rotate-180': activeDrawer === 'drawerCash'}"></i>
                    </button>
                    
                    <div v-show="activeDrawer === 'drawerCash'" class="hidden-drawer bg-light bg-opacity-50">
                        <div class="p-3 pt-0">
                            <div class="d-flex flex-column gap-3 pt-3">
                                <stats-card title="Realized Revenue" :value="'₱' + stats.realizedSalesToday" subtitle="Cash + Digital + Collections" icon="fas fa-coins" color="primary"></stats-card>
                                <stats-card title="Debt Collections" :value="'₱' + stats.debtCollectionsToday" subtitle="Collected Today" icon="fas fa-hand-holding-usd" color="info"></stats-card>
                                <stats-card title="Cash in Drawer" :value="'₱' + stats.estCashInDrawer" subtitle="Expected Cash" icon="fas fa-wallet" color="success"></stats-card>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DRAWER 2: PERFORMANCE -->
                <div class="border-0 rounded-4 shadow-sm overflow-hidden glass-panel">
                    <button class="w-100 bg-transparent py-4 px-4 shadow-none border-0 text-start d-flex align-items-center" type="button" @click="toggleDrawer('drawerPerf')">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="fas fa-chart-line text-success" style="font-size: 1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-0">Performance</h6>
                            <small class="text-muted">Net Profit: <span class="fw-bold text-success">₱{{ stats.profitToday }}</span></small>
                        </div>
                        <i class="fas fa-chevron-down text-muted transition-transform" :class="{'rotate-180': activeDrawer === 'drawerPerf'}"></i>
                    </button>
                    
                    <div v-show="activeDrawer === 'drawerPerf'" class="hidden-drawer bg-light bg-opacity-50">
                        <div class="p-3 pt-0">
                            <div class="d-flex flex-column gap-3 pt-3">
                                <stats-card title="Profit (Today)" :value="'₱' + stats.profitToday" subtitle="Net Income" icon="fas fa-coins" color="success"></stats-card>
                                <stats-card title="Monthly Sales" :value="'₱' + stats.salesMonth" subtitle="Accrual Basis" icon="fas fa-chart-line" color="primary"></stats-card>
                                <stats-card title="Total Collectibles" :value="'₱' + stats.totalCredits" subtitle="Unpaid Customer Debts" icon="fas fa-file-invoice-dollar" color="warning"></stats-card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        initialStats: {
            type: Object,
            required: true
        },
        storeId: {
            type: Number,
            required: true
        },
        outOfStockItems: {
            type: Number,
            required: true
        },
        canRestock: {
            type: Boolean,
            default: false
        },
        restockUrl: {
            type: String,
            default: '#'
        }
    },
    data() {
        return {
            stats: { ...this.initialStats },
            activeDrawer: null
        };
    },
    mounted() {
        console.log("DashboardStatsGrid mounted! Initializing Echo listener...");
        console.log("Store ID prop:", this.storeId);
        // Listen to Laravel Echo for real-time updates
        if (window.Echo) {
            console.log("window.Echo is available. Subscribing to admin-notifications...");
            window.Echo.channel('admin-notifications')
                .listen('.dashboard.need-refresh', (e) => {
                    console.log("DASHBOARD REFRESH EVENT RECEIVED!", e);
                    // Refresh data if store ID matches or is global (null / undefined)
                    if (!e.storeId || String(e.storeId) === String(this.storeId)) {
                        console.log("Store match! Fetching latest stats...");
                        this.fetchLatestStats();
                    } else {
                        console.log("Ignoring refresh event for different store.", e.storeId, this.storeId);
                    }
                });
        } else {
            console.warn("window.Echo is NOT available!");
        }
    },
    methods: {
        toggleDrawer(drawerId) {
            if (this.activeDrawer === drawerId) {
                this.activeDrawer = null;
            } else {
                this.activeDrawer = drawerId;
            }
        },
        async fetchLatestStats() {
            console.log("Fetching latest stats from API...");
            try {
                const response = await axios.get('/admin/api/dashboard/stats');
                console.log("Received new stats payload:", response.data);
                if (response.data) {
                    this.stats = response.data;
                }
            } catch (error) {
                console.error("Failed to fetch latest dashboard stats:", error);
            }
        }
    }
}
</script>

<style scoped>
.transition-transform {
    transition: transform 0.3s ease;
}
.rotate-180 {
    transform: rotate(180deg);
}
</style>
