<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        $folders = Folder::with('children', 'notes')
            ->where('user_id', auth()->id())
            ->whereNull('parent_id')
            ->get();

        return response()->json($folders);
    }

    public function show(Folder $folder)
    {
        $folder->load(['children', 'notes']);
        return response()->json($folder);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,improving,standardized',
        ]);

        $folder = Folder::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => auth()->id(),
            'description' => $validated['description'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'status' => $validated['status'] ?? 'draft',
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_folder',
            'target_type' => 'folder',
            'target_id' => $folder->id,
            'details' => "Created folder '{$folder->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($folder->load(['children', 'notes']));
    }

    public function update(Request $request, Folder $folder)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,improving,standardized',
        ]);
        
        // Prevent circular nesting
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            if ($this->isCircularNesting($folder->id, $validated['parent_id'])) {
                return response()->json(['error' => 'Cannot move folder into its own subfolder'], 400);
            }
        }
        
        $folder->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_folder',
            'target_type' => 'folder',
            'target_id' => $folder->id,
            'details' => "Updated folder '{$folder->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($folder);
    }
    
    private function isCircularNesting($folderId, $targetParentId)
    {
        if ($folderId == $targetParentId) {
            return true;
        }
        
        $parent = Folder::find($targetParentId);
        while ($parent) {
            if ($parent->id == $folderId) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }

    public function destroy(Folder $folder)
    {
        $folder->delete();

        return response()->json(['success' => true]);
    }

    public function tree()
    {
        $folders = Folder::with([
                'children.notes',
                'children.children.notes',
                'children.children.children.notes',
                'notes'
            ])
            ->where('user_id', auth()->id())
            ->whereNull('parent_id')
            ->get();

        return response()->json($folders);
    }
}
