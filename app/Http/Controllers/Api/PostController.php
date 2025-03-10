<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category', 'tags', 'user')->get();
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts',
            'content' => 'required',
            'image' => 'nullable|image',
            'affiliate_link' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
        ]);

        $post = Post::create($validated + ['user_id' => auth()->id()]);
        $post->tags()->sync($request->tags);

        return redirect()->route('posts.index')->with('success', 'Post creado exitosamente.');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts,slug,' . $post->id,
            'content' => 'required',
            'image' => 'nullable|image',
            'affiliate_link' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
        ]);

        $post->update($validated);
        $post->tags()->sync($request->tags);

        return redirect()->route('posts.index')->with('success', 'Post actualizado exitosamente.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post eliminado exitosamente.');
    }
}
