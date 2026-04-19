<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NotificationController;


// Public Routes

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Data
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('menu-items', MenuItemController::class)->only(['index', 'show']);
Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);

// Contact
Route::post('/contacts', [ContactController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    // Profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Authenticated User Routes
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'store']);
        Route::patch('/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);

        Route::get('/count', [CartController::class, 'count']);
    });
    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    // Bookings
    Route::prefix('bookings')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/me', [BookingController::class, 'myBookings']);
        Route::get('/{booking}', [BookingController::class, 'show']);
    });
});

// Admin Routes
Route::prefix('admin')
->name('admin.')
->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Users
    Route::get('/users', [UserController::class, 'allUsers']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/restore', [UserController::class, 'restore']);

    // Categories
    Route::apiResource('admin/categories', CategoryController::class);

    // Menu Items
    Route::apiResource('menu-items', MenuItemController::class);

    // Articles
    Route::apiResource('articles', ArticleController::class);

    // Orders
    Route::get('/orders', [OrderController::class, 'allOrders']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'allBookings']);
    Route::get('/bookings/{booking}', [BookingController::class, 'showBooking']); //
    Route::put('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    // Contacts
    Route::apiResource('contacts', ContactController::class)->except('store');
});
