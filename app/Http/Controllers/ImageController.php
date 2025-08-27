<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:posts,claims,avatars',
            'image' => 'required|image|max:2048',
        ]);

        $image = $request->image;
        $hashedName = Str::random(30) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('posts', $hashedName, 'public');

        $image = Image::create([
            "filename" => $path,
            'imageable_type' => Post::class,
        ]);
        return (new ImageResource($image))->response()->setStatusCode(201);
    }

}
