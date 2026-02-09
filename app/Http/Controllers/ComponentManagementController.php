<?php

namespace App\Http\Controllers;

use App\Models\Subsystem;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ComponentManagementController extends Controller
{
    public function create(Subsystem $subsystem)
    {
        return view('components.create', compact('subsystem'));
    }

    public function store(Request $request, Subsystem $subsystem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'health_status' => 'required|in:smooth,on_fire,needs_love',
            'current_issue' => 'nullable|string',
            'metric_value' => 'nullable|integer',
            'metric_label' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $validated['subsystem_id'] = $subsystem->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['order'] = $validated['order'] ?? $subsystem->components()->max('order') + 1;
        $validated['user_id'] = Auth::id();

        $component = Component::create($validated);

        return redirect()->route('subsystems.show', [
            'machineSlug' => $subsystem->machine->slug,
            'subsystemSlug' => $subsystem->slug
        ])->with('success', __('messages.component_created'));
    }

    public function edit(Component $component)
    {
        return view('components.edit', compact('component'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'health_status' => 'required|in:smooth,on_fire,needs_love',
            'current_issue' => 'nullable|string',
            'metric_value' => 'nullable|integer',
            'metric_label' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $component->update($validated);

        return redirect()->route('subsystems.show', [
            'machineSlug' => $component->subsystem->machine->slug,
            'subsystemSlug' => $component->subsystem->slug
        ])->with('success', __('messages.component_updated'));
    }

    public function destroy(Component $component)
    {
        $machineSlug = $component->subsystem->machine->slug;
        $subsystemSlug = $component->subsystem->slug;
        $component->delete();

        return redirect()->route('subsystems.show', [
            'machineSlug' => $machineSlug,
            'subsystemSlug' => $subsystemSlug
        ])->with('success', __('messages.component_deleted'));
    }
}
