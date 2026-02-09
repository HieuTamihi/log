<?php

namespace App\Http\Controllers;

use App\Models\Subsystem;
use App\Models\Streak;
use Illuminate\Support\Facades\Auth;

class SubsystemController extends Controller
{
    /**
     * Display a specific subsystem with its components (Level 3).
     */
    public function show($machineSlug, $subsystemSlug)
    {
        $subsystem = Subsystem::where('slug', $subsystemSlug)
            ->whereHas('machine', function ($query) use ($machineSlug) {
                $query->where('slug', $machineSlug);
            })
            ->with(['machine', 'components.upgrades'])
            ->firstOrFail();

        $streak = Streak::where('user_id', Auth::id())->first();

        return view('subsystems.show', compact('subsystem', 'streak'));
    }
}
