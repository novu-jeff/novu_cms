<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
{
    public function index()
    {
        // Get the first photo per album using MIN(id)
        $sub = DB::table('album_photo')
            ->select('album_id', DB::raw('MIN(id) as photo_id'))
            ->groupBy('album_id');

        $data = DB::table('albums')
            ->leftJoinSub($sub, 'ap', function ($join) {
                $join->on('albums.id', '=', 'ap.album_id');
            })
            ->leftJoin('album_photo', 'album_photo.id', '=', 'ap.photo_id')
            ->select('albums.*', 'album_photo.image_path')
            ->where('albums.isActive', true) 
            ->paginate(20);

        return view('photo-album.index', compact('data'));
    }

    public function loadData()
    {
        $sub = DB::table('album_photo')
            ->select('album_id', DB::raw('MIN(id) as photo_id'))
            ->groupBy('album_id');

        $data = DB::table('albums')
            ->leftJoinSub($sub, 'ap', function ($join) {
                $join->on('albums.id', '=', 'ap.album_id');
            })
            ->leftJoin('album_photo', 'album_photo.id', '=', 'ap.photo_id')
            ->select('albums.*', 'album_photo.image_path')
            ->where('albums.isActive', true)
            ->paginate(20);

        return response(['data' => $data, 'status', 'success']);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:30|unique:albums,name',
        ]);

        DB::beginTransaction();

        try {
            
            $album = Album::create([
                'name' => $validatedData['name'],
            ]);

            $redirectUrl = url("/photo-journals/photos/{$album->id}");

            DB::commit();

            return response([
                'data' => $album, 
                'redirect_url' => $redirectUrl,
                'message' => 'Album has been successfully created.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function  edit(string $id) 
    {
        $data = Album::where('isActive', true)->findOrFail($id);
        return response(['data' => $data, 'message' => 'success edit']);
    }

    public function destroy(string $id)
    {
        $data = Album::findOrFail($id);

        $data->isActive = false;
        $data->save();

        return response([
            'data' => $data,
            'message' => 'Member deactivated successfully.'
        ], 200);
    }

    public function update(Request $request, string $id)
    {
         $validatedData = $request->validate([
            'name' => 'required|string|max:30|unique:albums,name,' . $id,
        ]);

        DB::beginTransaction();

        try {
            $album = Album::findOrFail($id);

            // Update album name
            $album->update([
                'name' => $validatedData['name'],
            ]);

            DB::commit();

            return response([
                'data' => $album,
                'message' => 'Album has been successfully updated.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
