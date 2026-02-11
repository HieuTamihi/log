<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Machine;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index()
    {
        $countLogged = Log::count();
        $countInProgress = Log::where('status', 'in_progress')->count();
        $countNeedAction = Log::where('status', 'open')->count();

        $lastLog = Log::latest()->first();
        
        // Get machines with subsystems for zoom view
        $machines = Machine::with(['subsystems.components'])
            ->where('user_id', auth()->id())
            ->orderBy('order')
            ->get();

        return view('dashboard', compact(
            'countLogged',
            'countInProgress',
            'countNeedAction',
            'lastLog',
            'machines'
        ));
    }
}
