<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Streak;
use Illuminate\Support\Facades\Auth;

class ComponentController extends Controller
{
    /**
     * Display the specified component (Note View).
     */
    public function show($id)
    {
        $component = Component::with(['subsystem.machine', 'upgrades.user'])->findOrFail($id);
        $streak = Streak::where('user_id', Auth::id())->first();

        return view('components.show', compact('component', 'streak'));
    }
}
