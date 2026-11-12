<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $module = $request->get('module');

        $query = ActivityLog::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($module) {
            $query->where('module', $module);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return view('system.audit_logs.index', compact('logs', 'modules', 'search', 'module'));
    }
}

