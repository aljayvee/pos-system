<?php

namespace App\Enums;

enum Permission: string
{
    // Inventory
    case INVENTORY_VIEW = 'inventory.view';
    case INVENTORY_EDIT = 'inventory.edit';
    case INVENTORY_ADJUST = 'inventory.adjust';
    
    // Sales & POS
    case POS_ACCESS = 'pos.access';
    case SALES_VIEW = 'sales.view';
    case SALES_VOID = 'sales.void';
    case SALES_RETURN = 'sales.return';
    
    // Reports
    case REPORTS_VIEW = 'reports.view';
    case REPORTS_EXPORT = 'reports.export';

    // Administration
    case USER_MANAGE = 'user.manage';
    case SETTINGS_MANAGE = 'settings.manage';
    case LOGS_VIEW = 'logs.view';

    // Operational Overrides
    case PRICE_OVERRIDE = 'price.override';
    case REFUND_APPROVE = 'refund.approve';
    case USER_UNLOCK = 'user.unlock';

    public function label(): string
    {
        return match($this) {
            self::INVENTORY_VIEW => 'View product list and stock levels.',
            self::INVENTORY_EDIT => 'Add, edit, or delete products.',
            self::INVENTORY_ADJUST => 'Manually adjust stock counts (Audit).',
            self::POS_ACCESS => 'Access the Point of Sale screen.',
            self::SALES_VIEW => 'View sales history and receipts.',
            self::SALES_VOID => 'Void items or transactions after payment.',
            self::SALES_RETURN => 'Process customer returns and refunds.',
            self::REPORTS_VIEW => 'View sales and inventory reports.',
            self::REPORTS_EXPORT => 'Export data to PDF/Excel.',
            self::USER_MANAGE => 'Create, edit, or lock user accounts.',
            self::SETTINGS_MANAGE => 'Configure store settings and hardware.',
            self::LOGS_VIEW => 'View security and integrity logs.',
            self::PRICE_OVERRIDE => 'Manually change product price at POS.',
            self::REFUND_APPROVE => 'Authorize high-value refunds.',
            self::USER_UNLOCK => 'Remotely unlock a disabled user.',
        };
    }
}
