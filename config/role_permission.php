<?php

use App\Enums\Permission;

return [
    'admin' => [
            // Admin has EVERYTHING.
        Permission::INVENTORY_VIEW,
        Permission::INVENTORY_EDIT,
        Permission::INVENTORY_ADJUST,
        Permission::POS_ACCESS,
        Permission::SALES_VIEW,
        Permission::SALES_VOID,
        Permission::SALES_RETURN,
        Permission::REPORTS_VIEW,
        Permission::REPORTS_EXPORT,
        Permission::USER_MANAGE,
        Permission::SETTINGS_MANAGE,
        Permission::LOGS_VIEW,
        Permission::PRICE_OVERRIDE,
        Permission::REFUND_APPROVE,
        Permission::USER_UNLOCK
    ],

    'manager' => [
            // Operations & Overrides. No System Settings (Backup, API keys).
        Permission::INVENTORY_VIEW,
        Permission::INVENTORY_EDIT,
        Permission::INVENTORY_ADJUST,
        Permission::POS_ACCESS,
        Permission::SALES_VIEW,
        Permission::SALES_RETURN,
        Permission::REPORTS_VIEW,
        Permission::LOGS_VIEW,
        Permission::USER_MANAGE,       // Can manage staff
        Permission::USER_UNLOCK,
    ],

    'supervisor' => [
            // Sales Management
        Permission::POS_ACCESS,
        Permission::SALES_VIEW,
        Permission::SALES_VOID,
        Permission::SALES_RETURN,
        Permission::REPORTS_VIEW,      // Sales reports
        Permission::INVENTORY_VIEW,    // View stock to assist customers
        Permission::REFUND_APPROVE,
    ],

    'stock_clerk' => [
            // Inventory Only
        Permission::INVENTORY_VIEW,
        Permission::INVENTORY_EDIT,
        Permission::INVENTORY_ADJUST,
    ],

    'auditor' => [
            // Read-only Access (Global View)
        Permission::INVENTORY_VIEW,
        Permission::SALES_VIEW,
        Permission::REPORTS_VIEW,
        Permission::LOGS_VIEW,
        Permission::USER_MANAGE, // View users? Usually auditors need to see who is who. but not edit.
    ],

    'cashier' => [
            // Cashier View
        Permission::POS_ACCESS,
        Permission::INVENTORY_VIEW, // Lookup products
        Permission::SALES_VIEW,     // View own history
    ],
];
