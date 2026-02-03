<?php

namespace App\Http\Controllers;

use App\Models\Log;
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

        return view('dashboard', compact(
            'countLogged',
            'countInProgress',
            'countNeedAction',
            'lastLog'
        ));
    }
}
