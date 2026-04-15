<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $articles = Article::all();
        return $this->success("Articles retrieved successfully", $articles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('articles', 'public');
            $data['image_url'] = $imagePath;
        }

        $data['author_id'] = Auth::id();

        $article = Article::create($data);

        return $this->created("Article created successfully", $article);
    }

    public function show(Article $article)
    {
        return $this->success("Article retrieved successfully", $article);
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('image_url')) {

            if ($article->image_url) {
                Storage::disk('public')->delete($article->image_url);
            }

            $imagePath = $request->file('image_url')->store('articles', 'public');
            $data['image_url'] = $imagePath;
        }

        $article->update($data);

        return $this->success("Article updated successfully", $article);
    }

    public function destroy(Article $article)
    {
        if ($article->image_url) {
            Storage::disk('public')->delete($article->image_url);
        }

        $article->delete();

        return $this->deleted("Article deleted successfully");
    }
}
