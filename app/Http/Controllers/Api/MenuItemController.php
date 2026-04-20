<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MenuItemController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menuItems = MenuItem::with('category')->get();
        return $this->success("Menu Items retrieved successfully", $menuItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if ($request->hasFile('image_url')) {
        $uploadedFileUrl = Cloudinary::upload(
            $request->file('image_url')->getRealPath()
        )->getSecurePath();

        $data['image_url'] = $uploadedFileUrl;
    }

    $menuItem = MenuItem::create($data);

    return $this->created("Menu Item added successfully", $menuItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $menuItem = MenuItem::with('category')->findOrFail($id);
        return $this->success("Menu Item retrieved successfully", $menuItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|numeric',
            'category_id' => 'sometimes|exists:categories,id',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if ($request->hasFile('image_url')) {
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
            }
            $imagePath = $request->file('image_url')->store('menu_items', 'public');
            $data['image_url'] = $imagePath;
        }
        $menuItem->update($data);
        return $this->success("Menu Item updated successfully", $menuItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->image_url) {
            Storage::disk('public')->delete($menuItem->image_url);
        }
        $menuItem->delete();
        return $this->deleted("Menu Item deleted successfully", $menuItem);
    }
}
