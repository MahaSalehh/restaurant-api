<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())->get();

        if ($notifications->isEmpty()) {
            return $this->success('No notifications found', null);
        }

        return $this->success('Notifications retrieved successfully', $notifications);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return $this->forbidden();
        }

        $notification->update([
            'is_read' => true
        ]);

        return $this->success('Notification marked as read', $notification);
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return $this->forbidden();
        }

        $notification->delete();

        return $this->deleted('Notification deleted successfully');
    }
}
