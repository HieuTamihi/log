<?php

namespace App\Http\Controllers;

use App\Models\Canvas;
use Illuminate\Http\Request;

class CanvasController extends Controller
{
    public function index()
    {
        $canvases = Canvas::with(['folder', 'user'])->get();
        return response()->json($canvases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $canvas = Canvas::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'folder_id' => $validated['folder_id'] ?? null,
            'user_id' => auth()->id(),
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_canvas',
            'target_type' => 'canvas',
            'target_id' => $canvas->id,
            'details' => "Created canvas '{$canvas->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($canvas->load(['folder', 'user']));
    }

    public function show(Canvas $canvas)
    {
        $canvas->load(['cards.note', 'cards.linkedNotes', 'cards.linkedFolders', 'cards.folder.notes', 'folder', 'user']);

        // Merge linked notes and folders for each card
        $canvas->cards->each(function ($card) {
            $linkedItems = collect();
            
            if ($card->linkedNotes) {
                $linkedItems = $linkedItems->merge($card->linkedNotes);
            }
            
            if ($card->linkedFolders) {
                $linkedItems = $linkedItems->merge($card->linkedFolders);
            }
            
            $card->linked_notes = $linkedItems;
            unset($card->linkedNotes);
            unset($card->linkedFolders);
        });

        return response()->json($canvas);
    }

    public function update(Request $request, Canvas $canvas)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $canvas->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_canvas',
            'target_type' => 'canvas',
            'target_id' => $canvas->id,
            'details' => "Updated canvas '{$canvas->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($canvas->load(['folder', 'user']));
    }

    public function destroy(Canvas $canvas)
    {
        $canvas->delete();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_canvas',
            'target_type' => 'canvas',
            'target_id' => $canvas->id,
            'details' => "Deleted canvas '{$canvas->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json(['success' => true]);
    }
}
