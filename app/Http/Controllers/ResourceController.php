<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::with('uploader');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderBy('created_at', 'desc')->get();

        // Add full URL to each resource
        $resources->each(function($resource) {
            $resource->url = $resource->getUrlAttribute();
            $resource->formatted_size = $resource->getFormattedSizeAttribute();
        });

        return response()->json($resources);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('resources', $filename, 'public');

        // Determine type based on mime type
        $mimeType = $file->getMimeType();
        $type = 'other';
        $category = 'other';
        
        if (str_starts_with($mimeType, 'image/')) {
            $type = 'image';
            $category = 'images';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $type = 'video';
            $category = 'videos';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $type = 'audio';
            $category = 'audio';
        } elseif (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/markdown'
        ])) {
            $type = 'document';
            $category = 'documents';
        }

        $resource = Resource::create([
            'name' => $request->name ?? $file->getClientOriginalName(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'path' => $path,
            'type' => $type,
            'description' => $request->description,
            'category' => $request->category ?? $category,
            'tags' => $request->tags ?? [],
            'uploaded_by' => auth()->id(),
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'upload_resource',
            'target_type' => 'resource',
            'target_id' => $resource->id,
            'details' => "Uploaded resource '{$resource->name}'",
            'ip_address' => request()->ip(),
        ]);

        return response()->json($resource->load('uploader'));
    }

    public function show(Resource $resource)
    {
        // Update access tracking
        $resource->increment('download_count');
        $resource->update(['last_accessed_at' => now()]);

        return response()->json($resource->load('uploader'));
    }

    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        $resource->update($validated);

        return response()->json($resource->load('uploader'));
    }

    public function destroy(Resource $resource)
    {
        // Delete file from storage
        Storage::disk('public')->delete($resource->path);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_resource',
            'target_type' => 'resource',
            'target_id' => $resource->id,
            'details' => "Deleted resource '{$resource->name}'",
            'ip_address' => request()->ip(),
        ]);

        $resource->delete();

        return response()->json(['success' => true]);
    }

    // Get resource categories
    public function categories()
    {
        $categories = Resource::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return response()->json($categories);
    }

    // Download resource
    public function download(Resource $resource)
    {
        $resource->increment('download_count');
        $resource->update(['last_accessed_at' => now()]);

        $filePath = storage_path('app/public/' . $resource->path);
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, $resource->original_filename);
    }
}
