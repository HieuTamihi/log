<?php

namespace App\Http\Controllers;

use App\Models\MachineConnection;
use Illuminate\Http\Request;

class MachineConnectionController extends Controller
{
    public function index()
    {
        $connections = MachineConnection::where('user_id', auth()->id())
            ->with(['fromMachine', 'toMachine'])
            ->get();
        
        return response()->json($connections);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_machine_id' => 'required|exists:machines,id',
            'to_machine_id' => 'required|exists:machines,id|different:from_machine_id',
            'label' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $connection = MachineConnection::updateOrCreate(
            [
                'from_machine_id' => $validated['from_machine_id'],
                'to_machine_id' => $validated['to_machine_id'],
                'user_id' => auth()->id(),
            ],
            [
                'label' => $validated['label'] ?? null,
                'color' => $validated['color'] ?? '#6366f1',
            ]
        );

        return response()->json($connection->load(['fromMachine', 'toMachine']));
    }

    public function destroy($id)
    {
        $connection = MachineConnection::where('user_id', auth()->id())
            ->findOrFail($id);
        
        $connection->delete();
        
        return response()->json(['message' => 'Connection deleted']);
    }

    public function cleanup()
    {
        // Delete duplicate connections (keep only the first one)
        $duplicates = MachineConnection::where('user_id', auth()->id())
            ->select('from_machine_id', 'to_machine_id')
            ->groupBy('from_machine_id', 'to_machine_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $deletedCount = 0;
        foreach ($duplicates as $dup) {
            $connections = MachineConnection::where('user_id', auth()->id())
                ->where('from_machine_id', $dup->from_machine_id)
                ->where('to_machine_id', $dup->to_machine_id)
                ->orderBy('id')
                ->get();

            // Keep first, delete rest
            foreach ($connections->skip(1) as $conn) {
                $conn->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'message' => "Deleted $deletedCount duplicate connections",
            'deleted' => $deletedCount
        ]);
    }
}
