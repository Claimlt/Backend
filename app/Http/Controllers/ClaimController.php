<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Models\Claim;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Models\Image;
use App\Models\Post;
use Auth;
use Illuminate\Support\Facades\Gate;
use Storage;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $claim = Claim::with('images', 'user')->get();
        return ClaimResource::collection($claim);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClaimRequest $request)
    {
        $request->validated();
        $claim = Claim::create([
            'message' => $request->message,
            'post_id' => $request->post,
            'user_id' => Auth::user()->id
        ]);

        if ($request->filled('images')) {
            Image::whereIn('id', $request->input('images'))
                ->update([
                    'imageable_id' => $claim->id,
                    'imageable_type' => Claim::class,
                ]);
        }


        return (new ClaimResource($claim->load('images', 'user')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Claim $claim)
    {
        return new ClaimResource($claim->load('images', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClaimRequest $request, Claim $claim)
    {
        Gate::authorize('update', $claim);
        $request->validated();
        $claim->update([
            'message' => $request->message,
        ]);

        return new ClaimResource($claim->load('images', 'user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Claim $claim)
    {
        Gate::authorize('delete', $claim);

        $images = $claim->images;

        foreach ($images as $image) {
            // Delete file from storage
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->filename);

            // Delete image record
            $image->delete();
        }

        // Delete the claim itself
        $claim->delete();
        return response()->noContent();
    }

    public function getByUser()
    {
        return ClaimResource::collection(\Illuminate\Support\Facades\Auth::user()->claims);
    }

    public function getByPost(Post $post)
    {
        return ClaimResource::collection($post->claims);
    }
    public function getByUserPosts()
    {
        $postIds = Auth::user()->posts->pluck('id');
        $claims = Claim::whereIn('post_id', $postIds)->get();

        return ClaimResource::collection($claims);
    }


    public function approve(Claim $claim)
    {
        // Gate::authorize('approve', $claim);
        $claim->update([
            'approved_at' => now(),
            'approver_id' => \Illuminate\Support\Facades\Auth::user()->id
        ]);

        return new ClaimResource($claim->load('images', 'user'));
    }

}
