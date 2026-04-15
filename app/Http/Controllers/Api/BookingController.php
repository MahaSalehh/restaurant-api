<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|digits_between:10,15',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required',
            'guests' => 'required|integer|min:1'
        ]);

        $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->booking_date . ' ' . $request->booking_time);

        if ($bookingDateTime->isPast()) {
            return $this->error('Booking date and time must be in the future');
        }
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'phone' => $request->phone,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'guests' => $request->guests,
            'status' => 'pending'
        ]);
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => 'New booking from ' . Auth::user()->name,
            ]);
        }
        return $this->success('Booking created successfully', $booking);
    }

    public function myBookings()
    {
        $bookings = Booking::where('user_id', Auth::id())->get();
        return $this->success('Your bookings retrieved successfully', $bookings);
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return $this->forbidden();
        }

        return $this->success('Booking retrieved successfully', $booking);
    }

    public function allBookings()
    {
        $bookings = Booking::with('user')->get();
        return $this->success('All bookings retrieved successfully', $bookings);
    }

    public function showBooking(Booking $booking)
    {
        return $this->success('Booking retrieved successfully', $booking->load('user'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $booking->update([
            'status' => $request->status
        ]);
        Notification::create([
            'user_id' => $booking->user_id,
            'message' => 'Your booking #' . $booking->id . ' is ' . $request->status,
        ]);
        return $this->success('Booking status updated', $booking);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return $this->deleted('Booking deleted successfully');
    }
}
