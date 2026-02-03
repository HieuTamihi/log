<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    /**
     * Display list of logs with pagination and filtering.
     */
    public function index(Request $request)
    {
        $perPage = 20;
        
        $logs = Log::with(['user', 'solution.user'])
            ->latest()
            ->paginate($perPage);

        $countLogged = Log::count();
        $countInProgress = Log::where('status', 'in_progress')->count();
        $countNeedAction = Log::where('status', 'open')->count();

        return view('logs.index', compact(
            'logs',
            'countLogged',
            'countInProgress',
            'countNeedAction'
        ));
    }

    /**
     * Store a newly created log (from wizard).
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('LogController@store called');
        \Log::info('Request data:', $request->all());
        \Log::info('Auth check:', ['authenticated' => Auth::check(), 'user_id' => Auth::id()]);

        // Check if user is authenticated
        if (!Auth::check()) {
            \Log::warning('User not authenticated');
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tạo vấn đề!');
        }

        $request->validate([
            'log_content' => 'required|string',
            'emotion_level' => 'nullable|in:frustrated,annoyed,neutral',
        ]);

        $content = trim($request->log_content);
        $emotion = $request->emotion_level;

        // Add emotion prefix to content
        if ($emotion) {
            $emotionLabels = [
                'frustrated' => 'Rất khó chịu',
                'annoyed' => 'Hơi khó chịu',
                'neutral' => 'Bình thường'
            ];
            $content = "[{$emotionLabels[$emotion]}] {$content}";
        }

        // Generate name from content
        $name = mb_substr(strip_tags($content), 0, 50);
        if (mb_strlen(strip_tags($content)) > 50) {
            $name .= '...';
        }

        // Map emotion to status
        $statusMap = [
            'frustrated' => 'open',
            'annoyed' => 'in_progress',
            'neutral' => 'in_progress'
        ];
        $status = $statusMap[$emotion] ?? 'open';

        // Get user ID and ensure it's an integer
        $userId = Auth::id();
        
        if (!$userId || !is_numeric($userId)) {
            \Log::error('Invalid user ID:', ['user_id' => $userId]);
            return redirect()->route('login')->with('error', 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại!');
        }

        try {
            $logData = [
                'name' => $name,
                'content' => $content,
                'version' => '1.0',
                'status' => $status,
                'user_id' => (int) $userId,
            ];
            
            \Log::info('Creating log with data:', $logData);
            
            $log = Log::create($logData);
            
            \Log::info('Log created successfully:', ['log_id' => $log->id]);

            return redirect()->route('dashboard')->with('success', 'Đã lưu vấn đề!');
        } catch (\Exception $e) {
            \Log::error('Error creating log: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('dashboard')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified log.
     */
    public function update(Request $request, Log $log)
    {
        // Check ownership
        if ($log->user_id !== Auth::id()) {
            return back()->with('error', 'Bạn không có quyền sửa vấn đề này!');
        }

        $request->validate([
            'log_content' => 'required|string',
            'emotion_level' => 'nullable|in:frustrated,annoyed,neutral',
        ]);

        $content = trim($request->log_content);
        $emotion = $request->emotion_level;

        // Add emotion prefix to content
        if ($emotion) {
            $emotionLabels = [
                'frustrated' => 'Rất khó chịu',
                'annoyed' => 'Hơi khó chịu',
                'neutral' => 'Bình thường'
            ];
            $content = "[{$emotionLabels[$emotion]}] {$content}";
        }

        // Regenerate name from content
        $name = mb_substr(strip_tags($content), 0, 50);
        if (mb_strlen(strip_tags($content)) > 50) {
            $name .= '...';
        }

        $log->update([
            'name' => $name,
            'content' => $content,
        ]);

        return back()->with('success', 'Đã cập nhật vấn đề!');
    }

    /**
     * Remove the specified log.
     */
    public function destroy(Log $log)
    {
        // Check ownership
        if ($log->user_id !== Auth::id()) {
            return back()->with('error', 'Bạn không có quyền xóa vấn đề này!');
        }

        // Check if log has solution
        if ($log->solution) {
            return back()->with('error', 'Không thể xóa vấn đề đã có giải pháp!');
        }

        $log->delete();

        return back()->with('success', 'Đã xóa vấn đề!');
    }
}
