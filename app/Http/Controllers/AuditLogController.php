<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        // Search by details, user, or IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('details', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by target type
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Fetch logs, paginate
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Get unique actions and target types for filters
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $targetTypes = AuditLog::select('target_type')->distinct()->pluck('target_type');

        return view('audit_logs.index', compact('logs', 'actions', 'targetTypes'));
    }
}
