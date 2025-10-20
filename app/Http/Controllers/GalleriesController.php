<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;

class GalleriesController extends Controller
{
    public function index()
    {
        $photos = Gallery::latest()->get();
        return view('gallery.index', compact('photos'));
    }

    public function store(Request $request)
    {

      //  dd($request->file('images'));
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'title' => 'nullable|string|max:255'
         ]);

        // If multiple files are uploaded
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('galleries', 'public');

                Gallery::create([
                    'title' => $request->title,
                    'image' => $path
                ]);
            }
        }

        return back()->with('success', 'Photos uploaded successfully!');
    }

    public function destroy($id)
    {
        $photo = Gallery::find($id);

        if (!$photo) {
            return response()->json(['error' => 'Photo not found'], 404);
        }

        $filePath = storage_path('app/public/' . $photo->image);

        if ($photo->image && file_exists($filePath)) {
            @unlink($filePath); // the @ suppresses warnings
        }

        $photo->delete();

        return response()->json(['message' => 'Photo deleted successfully'], 200);
    }

    public function apiIndex()
    {
        $photos = Gallery::latest()->get()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'title' => $photo->title ?? 'Untitled',
                'image_url' => asset('storage/' . $photo->image),
                'active' => $photo->active,
                'created_at' => $photo->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $photos->count(),
            'photos' => $photos
        ]);
    }

    public function apiFrontGal()
    {
            $photos = Gallery::where('active', true)
            ->latest()
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'title' => $photo->title ?? 'Untitled',
                    'image_url' => asset('storage/' . $photo->image),
                    'created_at' => $photo->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'count' => $photos->count(),
            'photos' => $photos
        ]);
    }

    public function toggleActive($id)
    {
        $photo = Gallery::find($id);

        if (!$photo) {
            return response()->json(['error' => 'Photo not found'], 404);
        }

        $photo->active = !$photo->active;
        $photo->save();

        return response()->json([
            'message' => 'Photo status updated successfully.',
            'active' => $photo->active
        ]);
    }

    public function togglePhotoActive(Request $request, $id)
    {
        $photo = Gallery::findOrFail($id);
        $photo->active = $request->active;
        $photo->save();

        return response()->json(['success' => true, 'active' => $photo->active]);
    }

}
