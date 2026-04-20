<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MenuItemController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $menuItems = MenuItem::with('category')->get();
        return $this->success("Menu Items retrieved successfully", $menuItems);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|file|image|max:2048'
        ]);

        if ($request->hasFile('image_url')) {

            $uploaded = Cloudinary::upload(
                $request->file('image_url')->getRealPath()
            );

            $data['image_url'] = $uploaded->getSecurePath();
            $data['public_id'] = $uploaded->getPublicId();
        }

        $menuItem = MenuItem::create($data);

        return $this->created("Menu Item added successfully", $menuItem);
    }

    public function show(string $id)
    {
        $menuItem = MenuItem::with('category')->findOrFail($id);
        return $this->success("Menu Item retrieved successfully", $menuItem);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|numeric',
            'category_id' => 'sometimes|exists:categories,id',
            'image_url' => 'nullable|file|image|max:2048'
        ]);

        if ($request->hasFile('image_url')) {

            if ($menuItem->public_id) {
                Cloudinary::destroy($menuItem->public_id);
            }

            $uploaded = Cloudinary::upload(
                $request->file('image_url')->getRealPath()
            );

            $data['image_url'] = $uploaded->getSecurePath();
            $data['public_id'] = $uploaded->getPublicId();
        }

        $menuItem->update($data);

        return $this->success("Menu Item updated successfully", $menuItem);
    }

    public function destroy(MenuItem $menuItem)
    {
        
        if ($menuItem->public_id) {
            Cloudinary::destroy($menuItem->public_id);
        }

        $menuItem->delete();

        return $this->deleted("Menu Item deleted successfully", $menuItem);
    }
}
