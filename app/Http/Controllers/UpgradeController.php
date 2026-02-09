<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Upgrade;
use App\Models\Streak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpgradeController extends Controller
{
    /**
     * Show the form for creating a new upgrade.
     */
    public function create($componentId)
    {
        $component = Component::with(['subsystem.machine'])->findOrFail($componentId);
        $streak = Streak::where('user_id', Auth::id())->first();

        return view('upgrades.create', compact('component', 'streak'));
    }

    /**
     * Store a newly created upgrade.
     */
    public function store(Request $request)
    {
        $request->validate([
            'component_id' => 'required|exists:components,id',
            'name' => 'required|string|max:255',
            'purpose' => 'nullable|string',
            'trigger' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*' => 'string',
            'definition_of_done' => 'nullable|string',
        ]);

        $upgrade = Upgrade::create([
            'component_id' => $request->component_id,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'purpose' => $request->purpose,
            'trigger' => $request->trigger,
            'steps' => $request->steps ? array_values(array_filter($request->steps)) : [],
            'definition_of_done' => $request->definition_of_done,
            'status' => 'draft',
        ]);

        return redirect()->route('upgrades.edit', $upgrade)
            ->with('success', 'Upgrade created!');
    }

    /**
     * Show the form for editing an upgrade.
     */
    public function edit(Upgrade $upgrade)
    {
        if ($upgrade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $upgrade->load(['component.subsystem.machine']);
        $streak = Streak::where('user_id', Auth::id())->first();

        return view('upgrades.edit', compact('upgrade', 'streak'));
    }

    /**
     * Update the specified upgrade.
     */
    public function update(Request $request, Upgrade $upgrade)
    {
        \Illuminate\Support\Facades\Log::info('UpgradeController@update hit', ['upgrade_id' => $upgrade->id, 'user_id' => Auth::id()]);
        if ($upgrade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'purpose' => 'nullable|string',
            'trigger' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*' => 'string',
            'definition_of_done' => 'nullable|string',
        ]);

        $upgrade->update([
            'name' => $request->name,
            'purpose' => $request->purpose,
            'trigger' => $request->trigger,
            'steps' => $request->steps ? array_values(array_filter($request->steps)) : [],
            'definition_of_done' => $request->definition_of_done,
        ]);

        return back()->with('success', 'Upgrade updated!');
    }

    /**
     * Ship the upgrade (mark as active).
     */
    public function ship(Upgrade $upgrade)
    {
        if ($upgrade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $upgrade->ship();

        return redirect()->route('subsystems.show', [
            'machineSlug' => $upgrade->component->subsystem->machine->slug,
            'subsystemSlug' => $upgrade->component->subsystem->slug,
        ])->with('success', '🎉 Boom! Upgrade shipped successfully!');
    }

    /**
     * Remove the specified upgrade.
     */
    public function destroy(Upgrade $upgrade)
    {
        if ($upgrade->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $upgrade->delete();

        return back()->with('success', 'Upgrade deleted!');
    }
}
