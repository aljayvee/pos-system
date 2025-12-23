<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

use App\Services\LogIntegrityService;

class ActivityLogController extends Controller
{
    public function index(LogIntegrityService $integrityService)
    {
        // Check integrity on page load (lightweight enough for now, or cache it)
        $integrityStatus = $integrityService->verifyChain();

        $logs = ActivityLog::with('user')->latest()->paginate(20);
        return view('admin.logs.index', compact('logs', 'integrityStatus'));
    }
}