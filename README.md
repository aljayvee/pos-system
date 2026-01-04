# VeraPOS System Documentation

**Developer:** Aljayvee Versola
**Version:** 1.0.0

---

## 📖 Overview

**VeraPOS** is a high-performance, modern Point of Sale (POS) and Inventory Management System designed for retail businesses. Built with **Laravel 12** and **Vue.js**, it offers a seamless, mobile-first experience for cashiers while providing robust analytics and compliance tools for administrators.


---

## 🚀 Key Features

### 🛒 Point of Sale (Cashier)
-   **Mobile-First Design:** Fully responsive interface optimized for mobile devices with touch-friendly controls and bottom-sheet navigation.
-   **Barcode Scanning:** Integrated camera-based barcode scanner for mobile and support for USB scanners on desktop.
-   **Smart Cart Management:**
    -   Multi-buy pricing (Buy X Get Y, Wholesale/Tiered pricing).
    -   Stock validation in real-time.
    -   Support for multiple payment methods (Cash, E-Wallet, Credit/Utang).
-   **Customer Association:**
    -   Link sales to Walk-in or Registered Customers.
    -   "New Customer Utang" flow for credit sales.
-   **Offline Resiliency:** Service Worker support for basic offline functionality.

### 📦 Inventory Management
-   **Real-time Stock Tracking:** Automatic deduction upon sale.
-   **Stock Transfers:** Manage movement of goods between different store branches.
-   **Low Stock Alerts:** Visual badges and filters for items near reorder points.
-   **Product Variants:** Support for different sizes, colors, or units.

### 📊 Admin Dashboard & Analytics
-   **Sales Analytics:** Daily, weekly, and monthly visual graphs.
-   **Financial Reporting:** Cash vs. Accrual basis configuration.
-   **Top Products:** Identify best-selling items instantly.
-   **User Management:** Role-based access control (Admin, Manager, Cashier).

### 🔐 Security features
-   **MPIN Protection:** Secure sensitive actions (voids, returns, settings) with a 6-digit PIN.
-   **Role-Based Access:** Strict separation between Cashier and Admin functions.
-   **Register Locking:** Session-based register management (Open/Close Register).

---

## 🛠️ System Requirements

-   **PHP:** 8.1 or higher
-   **Composer:** 2.x
-   **Node.js:** 16.x or higher
-   **Database:** MySQL / MariaDB

---

## ⚙️ Installation & Setup Guide

Follow these steps to deploy VeraPOS on a new system.

### 1. Clone the Repository
```bash
git clone https://github.com/aljayvee/pos-system.git
cd pos-system
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
npm install
```

### 4. Environment Configuration
Duplicate the example environment file and configure your database credentials.
```bash
copy .env.example .env
```
Open `.env` and update the following:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_db
DB_USERNAME=root
DB_PASSWORD=

# Security Flags (Optional)
SAFETY_FLAG_BIR_COMPLIANCE=true
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Database Setup
Run migrations and seed the database with default roles, admin account, and test products.
```bash
php artisan migrate --seed
```
> **Default Admin Credentials:**
> - Email: `admin@pos.com`
> - Password: `Admin123456`

### 7. Build Assets
For development (Hot Reload):
```bash
npm run dev
```
For production:
```bash
npm run build
```

### 8. Run the Server
Start the local development server:
```bash
php artisan serve
```
Access the application at `http://127.0.0.1:8000`.

---

## 🧪 Running Tests

Ensure system stability by running the automated test suite.

```bash
php artisan test
```

---

## 📜 License

**Proprietary Software.**
Copyright © 2025-2026 Aljayvee Versola. All Rights Reserved.
Unauthorized copying, distribution, or modification of this code is strictly prohibited.
