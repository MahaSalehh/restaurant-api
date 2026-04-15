<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function profile()
    {
        $user = Auth::user();
        if (!$user) return $this->unauthorized('User not logged in');

        return $this->success('Profile retrieved successfully', $user);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) return $this->unauthorized('User not logged in');

        $request->validate([
            'name' => 'sometimes|string|max:255|min:2',
            'email' => 'sometimes|email||regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$/|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|regex:/^\+?\d{10,15}$/',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('email')) $data['email'] = $request->email;
        if ($request->has('phone')) $data['phone'] = $request->phone;
        if ($request->has('password')) $data['password'] = Hash::make($request->password);

        if (!empty($data)) {
            User::where('id', $user->id)->update($data);
        }

        return $this->success('Profile updated successfully', User::find($user->id));
    }

    public function allUsers()
    {
        $users = User::withTrashed()->get();
        return $this->success('All users retrieved successfully', $users);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        $user->update(['role' => $request->role]);
        return $this->success('User role updated successfully', $user);
    }

    public function destroy(User $user)
    {
        $user->delete(); // soft delete
        return $this->deleted('User deleted successfully (soft deleted)');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->find($id);
        if (!$user) return $this->notFound('User not found');

        $user->restore();
        return $this->success('User restored successfully', $user);
    }
}
