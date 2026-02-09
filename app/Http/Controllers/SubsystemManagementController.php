<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Subsystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SubsystemManagementController extends Controller
{
    public function create(Machine $machine)
    {
        return view('subsystems.create', compact('machine'));
    }

    public function store(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
        ]);

        $validated['machine_id'] = $machine->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['color'] = $validated['color'] ?? '#60a5fa';
        $validated['order'] = $validated['order'] ?? $machine->subsystems()->max('order') + 1;
        $validated['user_id'] = Auth::id();

        $subsystem = Subsystem::create($validated);

        return redirect()->route('machines.show', $machine->slug)
            ->with('success', __('messages.subsystem_created'));
    }

    public function edit(Subsystem $subsystem)
    {
        return view('subsystems.edit', compact('subsystem'));
    }

    public function update(Request $request, Subsystem $subsystem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $subsystem->update($validated);

        return redirect()->route('machines.show', $subsystem->machine->slug)
            ->with('success', __('messages.subsystem_updated'));
    }

    public function destroy(Subsystem $subsystem)
    {
        $machineSlug = $subsystem->machine->slug;
        $subsystem->delete();

        return redirect()->route('machines.show', $machineSlug)
            ->with('success', __('messages.subsystem_deleted'));
    }
}
