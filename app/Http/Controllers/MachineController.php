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
        $machines = Machine::with(['subsystems.components'])->orderBy('order', 'asc')->get();
        
        $streak = Streak::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_upgrades_shipped' => 0,
            ]
        );

        // Load connections
        $connections = \App\Models\MachineConnection::where('user_id', Auth::id())
            ->with(['fromMachine', 'toMachine'])
            ->get();

        return view('machines.index', compact('machines', 'streak', 'connections'));
    }

    /**
     * Display a specific machine with its subsystems (Level 2).
     */
    public function show($slug)
    {
        // Support both slug and ID lookup
        $machine = Machine::where('slug', $slug)
            ->orWhere('id', $slug)
            ->with(['user', 'subsystems', 'components'])
            ->firstOrFail();

        $streak = Streak::where('user_id', Auth::id())->first();

        return view('machines.show', compact('machine', 'streak'));
    }

    /**
     * Update machine coordinates.
     */
    public function updateCoordinates(\Illuminate\Http\Request $request, Machine $machine)
    {
        $request->validate([
            'position_x' => 'required|integer',
            'position_y' => 'required|integer',
        ]);

        $machine->update([
            'position_x' => $request->position_x,
            'position_y' => $request->position_y,
        ]);

        return response()->json(['success' => true]);
    }
    /**
     * Swap the order of two machines.
     */
    public function swapOrder(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'machine_id_1' => 'required|exists:machines,id',
            'machine_id_2' => 'required|exists:machines,id',
        ]);

        $machine1 = Machine::find($request->machine_id_1);
        $machine2 = Machine::find($request->machine_id_2);

        $tempOrder = $machine1->order;
        $machine1->update(['order' => $machine2->order]);
        $machine2->update(['order' => $tempOrder]);

        return response()->json(['success' => true]);
    }
}
