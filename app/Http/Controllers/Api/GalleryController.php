<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = GalleryItem::orderBy('sort_order')->orderBy('id');

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:photo,video',
            'file'        => 'required_if:type,photo|nullable|image|max:5120',
            'video_url'   => 'required_if:type,video|nullable|url',
            'thumbnail'   => 'nullable|image|max:2048',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('gallery', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('gallery/thumbs', 'public');
        }
        unset($validated['file'], $validated['thumbnail']);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = (GalleryItem::max('sort_order') ?? 0) + 1;
        }

        $item = GalleryItem::create($validated);

        return response()->json($item, 201);
    }

    public function update(Request $request, GalleryItem $galleryItem)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'sometimes|in:photo,video',
            'file'        => 'nullable|image|max:5120',
            'video_url'   => 'nullable|url',
            'thumbnail'   => 'nullable|image|max:2048',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($galleryItem->file_path) Storage::disk('public')->delete($galleryItem->file_path);
            $validated['file_path'] = $request->file('file')->store('gallery', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            if ($galleryItem->thumbnail_path) Storage::disk('public')->delete($galleryItem->thumbnail_path);
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('gallery/thumbs', 'public');
        }
        unset($validated['file'], $validated['thumbnail']);

        $galleryItem->update($validated);

        return response()->json($galleryItem->fresh());
    }

    public function destroy(GalleryItem $galleryItem)
    {
        if ($galleryItem->file_path) Storage::disk('public')->delete($galleryItem->file_path);
        if ($galleryItem->thumbnail_path) Storage::disk('public')->delete($galleryItem->thumbnail_path);
        $galleryItem->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items'              => 'required|array',
            'items.*.id'         => 'required|integer|exists:gallery_items,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            GalleryItem::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Orden actualizado']);
    }
}
