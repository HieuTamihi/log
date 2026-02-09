<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Streak;
use Illuminate\Support\Facades\Auth;

class MachineController extends Controller
{
    /**
     * Display the Business Machine dashboard (Level 1).
     */
    public function index()
    {
        $machines = Machine::with(['subsystems.components'])->orderBy('order')->get();
        
        $streak = Streak::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_upgrades_shipped' => 0,
            ]
        );

        return view('machines.index', compact('machines', 'streak'));
    }

    /**
     * Display a specific machine with its subsystems (Level 2).
     */
    public function show($slug)
    {
        // Support both slug and ID lookup
        $machine = Machine::where('slug', $slug)
            ->orWhere('id', $slug)
            ->with(['user', 'subsystems.user', 'subsystems.components.upgrades.user'])
            ->firstOrFail();

        $streak = Streak::where('user_id', Auth::id())->first();

        return view('machines.show', compact('machine', 'streak'));
    }
}
