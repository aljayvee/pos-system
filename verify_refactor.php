<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Manually boot the kernel to ensure providers are loaded
$app->make(Kernel::class)->bootstrap();

echo "--- Refactoring Verification Script ---\n";

$services = [
    \App\Services\Analytics\ForecastingService::class,
    \App\Services\Finance\AccountingService::class,
    \App\Services\Reports\ReportExportService::class,
    \App\Services\System\SystemUpdateService::class,
    \App\Services\Inventory\ProductImportService::class,
];

$failed = false;

foreach ($services as $service) {
    try {
        $instance = $app->make($service);
        echo "[PASS] Resolved $service\n";

        // Basic method check (reflection) to ensure methods exist
        $reflector = new ReflectionClass($service);
        foreach ($reflector->getMethods() as $method) {
            if ($method->class == $service && $method->isPublic()) {
                echo "       - Method: " . $method->name . "\n";
            }
        }

    } catch (\Exception $e) {
        $failed = true;
        echo "[FAIL] Could not resolve $service\n";
        echo "       Error: " . $e->getMessage() . "\n";
    }
}

// Controller Resolution Check
$controllers = [
    \App\Http\Controllers\Admin\ReportController::class,
    \App\Http\Controllers\Admin\StorePreferencesController::class,
    \App\Http\Controllers\Admin\ProductController::class,
];

foreach ($controllers as $controller) {
    try {
        $instance = $app->make($controller);
        echo "[PASS] Resolved $controller\n";
    } catch (\Exception $e) {
        $failed = true;
        echo "[FAIL] Could not resolve $controller\n";
        echo "       Error: " . $e->getMessage() . "\n";
    }
}

if ($failed) {
    echo "\nVerification FAILED.\n";
    exit(1);
} else {
    echo "\nVerification PASSED.\n";
    exit(0);
}
