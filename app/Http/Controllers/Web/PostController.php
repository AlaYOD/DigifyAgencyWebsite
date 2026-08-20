<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicPostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $locale = app()->getLocale();
        $post = Post::published()->with('category')->where("slug->{$locale}", $slug)->firstOrFail();
        $post->increment('views_count');

        return Inertia::render('Posts/Show', ['post' => (new PublicPostResource($post))->resolve($request)]);
    }
}
