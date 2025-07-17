<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\AlbumPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlbumPhotoController extends Controller
{

    /**
     * Show the form for editing the specified resource.
     */
    public function index(string $id)
    {
        $data = Album::where('isActive', true)->findOrFail($id);

        return view('photo-album.photo.index', compact('data'));
    }

    public function loadImages(string $id)
    {
        $parent = DB::table('albums')->where('id', $id)->first();
        $data = DB::table('album_photo')
            ->where('isActive', true)
            ->where('album_id', $id)
            ->get();

        return response(['data' => $data, 'parent' => $parent, 'status' => 'success']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $uploadedImages = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('assignments', 'public');

                    DB::table('album_photo')->insert([
                        'album_id'   => $request->album_id,
                        'image_path' => $path,
                        'isActive'   => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $uploadedImages[] = $path;
                }
            } else {
                return response()->json([
                    'message' => 'No image files were uploaded.',
                    'status' => 'error',
                ], 422);
            }

            DB::commit();

            return response()->json([
                'message' => 'Images uploaded successfully.',
                'data' => $uploadedImages,
                'status' => 'success',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Upload failed.',
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $data = AlbumPhoto::where('isActive', true)->findOrFail($id);

        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Image deleted successfully.'
        ], 200);
    }

}
