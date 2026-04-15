<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with('cartItems.menuItem')
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return $this->success('Cart is empty', [
                'cart_items' => [],
                'total_price' => 0
            ]);
        }

        $total = 0;

        foreach ($cart->cartItems as $item) {
            $total += $item->quantity * $item->menuItem->price;
        }

        return $this->success('Cart retrieved successfully', [
            'cart_items' => $cart->cartItems,
            'total_price' => $total
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'menu_item_id' => $request->menu_item_id,
                'quantity' => $request->quantity
            ]);
        }

        return $this->created('Item added to cart successfully', $item);
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->error('Cart not found', 404);
        }

        $item = CartItem::where('id', $id)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$item) {
            return $this->error('Item not found in cart', 404);
        }

        $item->update([
            'quantity' => $request->quantity
        ]);

        return $this->success('Item updated successfully', $item);
    }

    public function removeItem($id)
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->error('Cart not found', 404);
        }

        $item = CartItem::where('id', $id)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$item) {
            return $this->error('Item not found in cart', 404);
        }

        $item->delete();

        return $this->deleted('Item removed successfully');
    }

    public function clear()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->success('Cart is already empty', null);
        }

        CartItem::where('cart_id', $cart->id)->delete();

        return $this->deleted('Cart cleared successfully');
    }
}
