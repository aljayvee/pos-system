<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

use App\Services\LogIntegrityService;

class ActivityLogController extends Controller
{
    public function index(Request $request, LogIntegrityService $integrityService)
    {
        // Check integrity on page load (lightweight enough for now, or cache it)
        $integrityStatus = $integrityService->verifyChain();

        $query = ActivityLog::with('user')->latest();

        // 1. Search (Description or User Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter by Action
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        // 3. Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        return view('admin.logs.index', compact('logs', 'integrityStatus'));
    }
}