<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

use App\Models\NoteVersion;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::where('user_id', auth()->id());

        if ($request->has('root')) {
            $query->whereNull('folder_id');
        }

        $notes = $query->get();

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,improving,standardized',
        ]);

        $note = Note::create([
            'name' => $validated['name'],
            'content' => $validated['content'] ?? '',
            'folder_id' => $validated['folder_id'] ?? null,
            'user_id' => auth()->id(),
            'description' => $validated['description'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'current_version' => 1,
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_note',
            'target_type' => 'note',
            'target_id' => $note->id,
            'details' => "Created note '{$note->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($note);
    }

    public function show(Note $note)
    {
        return response()->json($note->load(['versions.user', 'manager']));
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,improving,standardized',
            'change_note' => 'nullable|string',
        ]);

        // Versioning Logic
        if (isset($validated['content']) && $validated['content'] !== $note->content) {
            // Archive current content as a version
            NoteVersion::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(), // User making the change
                'content' => $note->content, // Format: Save OLD content
                'version' => $note->current_version,
                'change_note' => $request->input('change_note', 'Auto-saved version'),
            ]);

            $note->current_version = $note->current_version + 1;
        }

        $note->update($validated); // This updates content to new value

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_note',
            'target_type' => 'note',
            'target_id' => $note->id,
            'details' => "Updated note '{$note->name}'" . (isset($validated['content']) ? " (Content changed)" : "") . (isset($validated['status']) ? " (Status: {$validated['status']})" : ""),
            'ip_address' => request()->ip(),
        ]);

        return response()->json($note->load(['versions.user', 'manager']));
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json(['success' => true]);
    }
}
