<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Image;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Storage;


class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('images', 'user')->get();
        return PostResource::collection($posts);
    }
    public function myPosts()
    {
        $user = Auth::user();

        $posts = Post::with('images')
            ->where('user_id', $user->id)
            ->get();

        return PostResource::collection($posts);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $request->validated();
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::user()->id
        ]);

        if ($request->filled('images')) {
            Image::whereIn('id', $request->input('images'))
                ->update([
                    'imageable_id' => $post->id,
                    'imageable_type' => Post::class,
                ]);
        }


        return (new PostResource($post->load('images')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post->load('images', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);
        $request->validated();
        $post->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return new PostResource($post->load('images'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $images = $post->images;

        foreach ($images as $image) {
            // Delete file from storage
            Storage::disk('public')->delete($image->filename);

            // Delete image record
            $image->delete();
        }

        // Delete the post itself
        $post->delete();
        return response()->noContent();
    }

    public function trendingTitles()
    {
        // Get all post titles
        $posts = Post::select('title')->get();

        $titleCount = [];

        foreach ($posts as $post) {
            $title = trim($post->title);
            if ($title) {
                $titleCount[$title] = ($titleCount[$title] ?? 0) + 1;
            }
        }

        // Sort by count (descending)
        arsort($titleCount);

        // Take top 5 titles
        $topTitles = array_slice($titleCount, 0, 5, true);

        // Format response
        $result = [];
        foreach ($topTitles as $title => $count) {
            $result[] = ['tag' => $title, 'count' => $count];
        }

        return response()->json(['data' => $result]);
    }


}
