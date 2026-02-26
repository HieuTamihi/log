<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

use App\Models\NoteVersion;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query();

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
            'status' => 'nullable|in:none,draft,improving,standardized',
        ]);

        $note = Note::create([
            'name' => $validated['name'],
            'content' => $validated['content'] ?? '',
            'folder_id' => $validated['folder_id'] ?? null,
            'user_id' => auth()->id(),
            'description' => $validated['description'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'status' => $validated['status'] ?? 'none',
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
        return response()->json($note->load(['versions.user', 'manager', 'attachments.uploader', 'tabs']));
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:none,draft,improving,standardized',
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

    // Attachment methods
    public function uploadAttachment(Request $request, Note $note)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('attachments', $filename, 'public');

        $type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';

        $attachment = \App\Models\NoteAttachment::create([
            'note_id' => $note->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'type' => $type,
            'uploaded_by' => auth()->id(),
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'upload_attachment',
            'target_type' => 'note',
            'target_id' => $note->id,
            'details' => "Uploaded attachment '{$attachment->original_filename}' to note '{$note->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($attachment->load('uploader'));
    }

    public function deleteAttachment(\App\Models\NoteAttachment $attachment)
    {
        \Storage::disk('public')->delete($attachment->path);
        
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_attachment',
            'target_type' => 'note',
            'target_id' => $attachment->note_id,
            'details' => "Deleted attachment '{$attachment->original_filename}'",
            'ip_address' => request()->ip(),
        ]);

        $attachment->delete();

        return response()->json(['success' => true]);
    }

    // Tab methods
    public function createTab(Request $request, Note $note)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $maxOrder = $note->tabs()->max('order') ?? -1;

        $tab = \App\Models\NoteTab::create([
            'note_id' => $note->id,
            'name' => $validated['name'],
            'content' => $validated['content'] ?? '',
            'order' => $maxOrder + 1,
        ]);

        return response()->json($tab);
    }

    public function updateTab(Request $request, \App\Models\NoteTab $tab)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $tab->update($validated);

        return response()->json($tab);
    }

    public function deleteTab(\App\Models\NoteTab $tab)
    {
        $tab->delete();
        return response()->json(['success' => true]);
    }

    public function reorderTabs(Request $request)
    {
        $validated = $request->validate([
            'tabs' => 'required|array',
            'tabs.*.id' => 'required|exists:note_tabs,id',
            'tabs.*.order' => 'required|integer',
        ]);

        foreach ($validated['tabs'] as $tabData) {
            \App\Models\NoteTab::where('id', $tabData['id'])->update(['order' => $tabData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
