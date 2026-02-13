<?php

namespace App\Http\Controllers;

use App\Models\NoteCard;
use App\Models\Note;
use Illuminate\Http\Request;

class GraphController extends Controller
{
    public function index()
    {
        $cards = NoteCard::with(['note', 'linkedNotes', 'linkedFolders', 'folder.notes'])
            ->where('user_id', auth()->id())
            ->get();

        // Merge linked notes and folders into a single array for frontend
        $cards->each(function ($card) {
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

        return view('graph.index', compact('cards'));
    }

    public function createCard(Request $request)
    {
        $validated = $request->validate([
            'note_id' => 'required_without:folder_id|exists:notes,id',
            'folder_id' => 'required_without:note_id|exists:folders,id',
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
        ]);

        $card = NoteCard::create([
            'note_id' => $validated['note_id'] ?? null,
            'folder_id' => $validated['folder_id'] ?? null,
            'user_id' => auth()->id(),
            'position_x' => $validated['position_x'],
            'position_y' => $validated['position_y'],
            'zoom_level' => 1,
        ]);

        $card->load(['note', 'linkedNotes', 'linkedFolders', 'folder.notes']);
        
        // Merge linked items
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

        return response()->json($card);
    }

    public function createFolderCard(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
        ]);

        $card = NoteCard::create([
            'folder_id' => $validated['folder_id'],
            'user_id' => auth()->id(),
            'position_x' => $validated['position_x'],
            'position_y' => $validated['position_y'],
            'zoom_level' => 1,
        ]);

        $card->load(['folder.notes', 'linkedNotes', 'linkedFolders']);
        
        // Merge linked items
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

        return response()->json($card);
    }

    public function updatePosition(Request $request, NoteCard $card)
    {
        $validated = $request->validate([
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
        ]);

        $card->update($validated);

        return response()->json($card);
    }

    public function addLink(Request $request, NoteCard $card)
    {
        $validated = $request->validate([
            'linked_note_id' => 'nullable|exists:notes,id',
            'linked_folder_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['linked_note_id'])) {
            $card->linkedNotes()->attach($validated['linked_note_id']);
        }
        
        if (!empty($validated['linked_folder_id'])) {
            $card->linkedFolders()->attach($validated['linked_folder_id']);
        }

        // Refresh relationships
        $card->load(['linkedNotes', 'linkedFolders']);

        // Return merged linked items
        $linkedItems = collect();
        if ($card->linkedNotes) {
            $linkedItems = $linkedItems->merge($card->linkedNotes);
        }
        if ($card->linkedFolders) {
            $linkedItems = $linkedItems->merge($card->linkedFolders);
        }
        
        return response()->json(['linked_notes' => $linkedItems]);
    }

    public function removeLink(Request $request, NoteCard $card)
    {
        $validated = $request->validate([
            'linked_note_id' => 'nullable|exists:notes,id',
            'linked_folder_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['linked_note_id'])) {
            $card->linkedNotes()->detach($validated['linked_note_id']);
        }
        
        if (!empty($validated['linked_folder_id'])) {
            $card->linkedFolders()->detach($validated['linked_folder_id']);
        }

        // Refresh relationships
        $card->load(['linkedNotes', 'linkedFolders']);

        // Return merged linked items
        $linkedItems = collect();
        if ($card->linkedNotes) {
            $linkedItems = $linkedItems->merge($card->linkedNotes);
        }
        if ($card->linkedFolders) {
            $linkedItems = $linkedItems->merge($card->linkedFolders);
        }
        
        return response()->json(['linked_notes' => $linkedItems]);
    }

    public function deleteCard(NoteCard $card)
    {
        $card->delete();

        return response()->json(['success' => true]);
    }
}
