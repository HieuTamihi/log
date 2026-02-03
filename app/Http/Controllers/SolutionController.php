<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolutionController extends Controller
{
    /**
     * Show the form for creating a new solution.
     */
    public function create(Request $request)
    {
        $logId = $request->query('log_id');
        
        // Get logs without solutions
        $logsWithoutSolution = Log::doesntHave('solution')->get();

        return view('solutions.create', compact('logsWithoutSolution', 'logId'));
    }

    /**
     * Store a newly created solution.
     */
    public function store(Request $request)
    {
        $request->validate([
            'log_id' => 'required|exists:logs,id',
            'solution_name' => 'required|string|max:255',
            'solution_content' => 'required|string',
            'solution_version' => 'nullable|string|max:50',
            'solution_status' => 'nullable|in:draft,testing,done',
        ]);

        // Check if log already has solution
        $log = Log::findOrFail($request->log_id);
        if ($log->solution) {
            return back()->with('error', 'Vấn đề này đã có giải pháp!');
        }

        $solution = Solution::create([
            'log_id' => $request->log_id,
            'name' => $request->solution_name,
            'content' => $request->solution_content,
            'version' => $request->solution_version ?? '1.0',
            'status' => $request->solution_status ?? 'draft',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('solutions.show', $solution)
            ->with('success', 'Tạo giải pháp thành công!');
    }

    /**
     * Display the specified solution.
     */
    public function show(Solution $solution)
    {
        $solution->load(['log', 'user', 'history' => function($query) {
            $query->latest('changed_at')->limit(3);
        }]);

        return view('solutions.show', compact('solution'));
    }

    /**
     * Update the specified solution.
     */
    public function update(Request $request, Solution $solution)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'nullable|string|max:50',
            'status' => 'nullable|in:draft,testing,done',
        ]);

        // Check if anything changed
        $hasChanges = $solution->name !== $request->name ||
                      $solution->content !== $request->content ||
                      $solution->version !== ($request->version ?? $solution->version) ||
                      $solution->status !== ($request->status ?? $solution->status);

        if ($hasChanges) {
            // Save current state to history
            $solution->saveToHistory();
        }

        $solution->update([
            'name' => $request->name,
            'content' => $request->content,
            'version' => $request->version ?? $solution->version,
            'status' => $request->status ?? $solution->status,
        ]);

        return redirect()->route('solutions.show', $solution)
            ->with('success', 'Cập nhật giải pháp thành công!');
    }
}
