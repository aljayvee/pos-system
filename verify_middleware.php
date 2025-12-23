<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;

$user = User::where('email', 'manager@pos.com')->first();
if (!$user) die("User not found");

Auth::login($user);
echo "Logged in as {$user->name} ({$user->role})\n";

$middleware = new RoleMiddleware();
$request = Request::create('/admin/dashboard', 'GET');

$next = function ($req) {
    echo "Middleware passed! (Called Next)\n";
    return "OK";
};

try {
    // Mimic route: role:admin,manager,supervisor,stock_clerk,auditor
    // Laravel passes varargs:
    $middleware->handle($request, $next, 'admin', 'manager', 'supervisor', 'stock_clerk', 'auditor');
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Middleware FAILED: " . $e->getMessage() . " (Status: " . $e->getStatusCode() . ")\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
