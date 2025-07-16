<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumPhotoController extends Controller
{

    /**
     * Show the form for editing the specified resource.
     */
    public function index(string $id)
    {
        return view('photo-album.photo.index');
    }
}
