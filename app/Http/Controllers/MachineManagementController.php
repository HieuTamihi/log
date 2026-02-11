<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MachineManagementController extends Controller
{
    public function index()
    {
        $machines = Machine::with('subsystems.components')->orderBy('order')->get();
        return view('machines.manage', compact('machines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header' => 'nullable|string|max:255',
            'sub_header' => 'nullable|string|max:255',
            'description' => 'required|string',
            'detail_description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $count = 1;
        while (Machine::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        $validated['slug'] = $slug;
        $validated['color'] = $validated['color'] ?? '#60a5fa';
        $validated['order'] = $validated['order'] ?? Machine::max('order') + 1;
        $validated['user_id'] = Auth::id();

        $machine = Machine::create($validated);

        return redirect()->route('dashboard')
            ->with('success', __('messages.machine_created'));
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header' => 'nullable|string|max:255',
            'sub_header' => 'nullable|string|max:255',
            'description' => 'required|string',
            'detail_description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'order' => 'nullable|integer',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Ensure slug is unique, excluding current machine
        $originalSlug = $slug;
        $count = 1;
        while (Machine::where('slug', $slug)->where('id', '!=', $machine->id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        $validated['slug'] = $slug;

        $machine->update($validated);

        return redirect()->route('dashboard')
            ->with('success', __('messages.machine_updated'));
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();

        return redirect()->route('dashboard')
            ->with('success', __('messages.machine_deleted'));
    }

    public function updateStatus(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'health_status' => 'required|string|in:green,red,yellow,auto',
        ]);

        $machine->update([
            'health_status' => $validated['health_status'] === 'auto' ? null : $validated['health_status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'health_status' => $machine->health_status,
            'status_color' => $machine->status_color,
            'status_icon' => $machine->status_icon
        ]);
    }
}
