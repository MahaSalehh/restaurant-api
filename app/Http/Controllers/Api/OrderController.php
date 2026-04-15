<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'phone' => 'required|string|regex:/^\+?\d{10,15}$/',
            'payment_method' => 'required|in:cash,card',
            'notes' => 'nullable|string|max:255'
        ]);

        $cart = Cart::where('user_id', Auth::id())
            ->with('cartItems.menuItem')
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return $this->notFound('Cart is empty, cannot place order');
        }

        $total = 0;
        $total = $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->menuItem->price;
        });

        $order = Order::create([
            'user_id' => Auth::id(),
            'address' => $request->address,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'total_price' => $total,
            'status' => 'pending'
        ]);

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => 'New order from ' . Auth::user()->name,
            ]);
        }

        foreach ($cart->cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item->menu_item_id,
                'quantity' => $item->quantity,
                'price' => $item->menuItem->price
            ]);
        }

        $cart->cartItems()->delete();

        return $this->created('Order placed successfully', $order->load('orderItems.menuItem'));
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('orderItems.menuItem')
            ->get();

        if ($orders->isEmpty()) {
            return $this->success('No orders found', null);
        }

        return $this->success('Orders retrieved successfully', $orders);
    }

    public function allOrders()
    {
        $orders = Order::with('orderItems.menuItem', 'user')->get();

        if ($orders->isEmpty()) {
            return $this->notFound('No orders found');
        }

        return $this->success('All orders retrieved successfully', $orders);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return $this->forbidden("You do not have permission to access this resource");
        }

        return $this->success('Order retrieved successfully', $order->load('orderItems.menuItem'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,in_progress,delivered,rejected'
        ]);

        $order->update([
            'status' => $request->status
        ]);
        Notification::create([
            'user_id' => $order->user_id,
            'message' => 'Your order #' . $order->id . ' is now ' . $request->status,
        ]);

        return $this->success('Order status updated successfully', $order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return $this->deleted('Order deleted successfully');
    }
}
