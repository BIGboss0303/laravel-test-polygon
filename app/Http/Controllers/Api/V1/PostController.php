<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Api\V1\Post\PostResource;
use App\Http\Resources\Api\V1\Post\PostCollection;
use App\Http\Requests\Api\V1\Post\StorePostRequest;
use App\Http\Requests\Api\V1\Post\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts_query = Post::query();
        if(isset($request->categoryId)){
            $posts_query->whereHas('categories', function($query) use($request){
                $query->where('category_id', $request->categoryId);
            });
        }
        $posts = $posts_query->orderBy('created_at')->get();
        return response()->json(new PostCollection($posts));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $validated['author_id'] = Auth::user()->id;
        DB::beginTransaction();
        try{
            $post = Post::create($validated);
            $post->categories()->attach($validated['categories']);
            DB::commit();
            return response()->json(new PostResource($post),201);
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }       
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json(new PostResource($post));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try{
            $post->update($validated);
            $post->categories()->sync($validated['categories']);
            DB::commit();
            return response()->json(new PostResource($post),200);
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(null, 204);
    }
}
