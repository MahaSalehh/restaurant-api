<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            'image_url' => 'nullable|file|image|max:2048'
        ]);

        if ($request->hasFile('image_url')) {

            $uploaded = Cloudinary::upload(
                $request->file('image_url')->getRealPath()
            );

            $data['image_url'] = $uploaded->getSecurePath();
            $data['public_id'] = $uploaded->getPublicId(); // 🔥 مهم جدًا
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
            'image_url' => 'nullable|file|image|max:2048'
        ]);

        if ($request->hasFile('image_url')) {

            if ($article->public_id) {
                Cloudinary::destroy($article->public_id);
            }

            $uploaded = Cloudinary::upload(
                $request->file('image_url')->getRealPath()
            );

            $data['image_url'] = $uploaded->getSecurePath();
            $data['public_id'] = $uploaded->getPublicId();
        }

        $article->update($data);

        return $this->success("Article updated successfully", $article);
    }

    public function destroy(Article $article)
    {
        if ($article->public_id) {
            Cloudinary::destroy($article->public_id);
        }

        $article->delete();

        return $this->deleted("Article deleted successfully");
    }
}
