<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - System Sight</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px; }
        
        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .filter-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-group label {
            font-size: 13px;
            font-weight: 500;
            color: #555;
        }
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #6366f1;
        }
        .filter-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary {
            background: #6366f1;
            color: white;
        }
        .btn-primary:hover {
            background: #4f46e5;
        }
        .btn-secondary {
            background: #e5e5e5;
            color: #555;
        }
        .btn-secondary:hover {
            background: #d4d4d4;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f1f1f1; font-weight: 600; color: #555; }
        tr:hover { background-color: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-create { background: #e6f4ea; color: #1e7e34; }
        .badge-update { background: #fff3cd; color: #856404; }
        .badge-delete { background: #f8d7da; color: #721c24; }
        .status-indicator { 
            display: inline-block; 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            margin-left: 6px;
            vertical-align: middle;
        }
        .status-draft { background: #ef4444; }
        .status-improving { background: #f59e0b; }
        .status-standardized { background: #10b981; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #666; font-weight: 500; margin-bottom: 20px; }
        .back-btn:hover { color: #333; }
        
        /* Pagination wrapper */
        .pagination { 
            margin-top: 30px; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            gap: 16px; 
        }
        
        /* Pagination nav container */
        .pagination nav { 
            display: flex; 
            align-items: center; 
            gap: 8px;
        }
        
        /* Pagination links and spans */
        .pagination a, 
        .pagination span { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            min-width: 36px; 
            height: 36px; 
            padding: 0 12px;
            text-decoration: none; 
            color: #555; 
            border: 1px solid #ddd; 
            border-radius: 6px;
            font-size: 14px;
            background: white;
            transition: all 0.15s;
        }
        
        /* Hover state */
        .pagination a:hover { 
            background: #f5f5f5; 
            border-color: #ccc;
            color: #333;
        }
        
        /* Active page */
        .pagination .active span { 
            background: #6366f1; 
            color: white; 
            border-color: #6366f1;
            font-weight: 600;
        }
        
        /* Disabled state */
        .pagination .disabled span { 
            color: #ccc; 
            cursor: not-allowed;
            background: #fafafa;
        }
        
        /* SVG icons in pagination */
        .pagination svg { 
            width: 14px; 
            height: 14px; 
        }
        
        /* Results text */
        .pagination p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        /* Hide default Laravel pagination text elements we don't want */
        .pagination .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Graph</a>
        
        <h1><i class="fa-solid fa-file-lines"></i> Audit Logs</h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('audit_logs.index') }}">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search by details, user, or IP..." value="{{ request('search') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label for="action">Action</label>
                        <select id="action" name="action">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="target_type">Target Type</label>
                        <select id="target_type" name="target_type">
                            <option value="">All Types</option>
                            @foreach($targetTypes as $type)
                                <option value="{{ $type }}" {{ request('target_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From Date</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To Date</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('audit_logs.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->user ? $log->user->username . ' (' . $log->user->role . ')' : 'Unknown' }}</td>
                        <td>
                            @php
                                $badgeClass = 'badge-update';
                                if (str_contains($log->action, 'create')) $badgeClass = 'badge-create';
                                if (str_contains($log->action, 'delete')) $badgeClass = 'badge-delete';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $log->action }}</span>
                        </td>
                        <td>
                            @php
                                // Extract status from details if present
                                $details = $log->details;
                                $statusIndicator = '';
                                
                                if (preg_match('/\(Status: (draft|improving|standardized)\)/', $details, $matches)) {
                                    $status = $matches[1];
                                    $statusClass = 'status-' . $status;
                                    $statusIndicator = '<span class="status-indicator ' . $statusClass . '" title="Status: ' . ucfirst($status) . '"></span>';
                                    // Remove status text from details
                                    $details = preg_replace('/\s*\(Status: (draft|improving|standardized)\)/', '', $details);
                                }
                            @endphp
                            {{ $details }}{!! $statusIndicator !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #999;">
                            No audit logs found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $logs->links() }}
        </div>
    </div>
</body>
</html>
