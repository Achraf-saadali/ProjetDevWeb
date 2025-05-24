<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // ✅ Get available seats for a specific showtime
    public function getBookedSeats(Showtime $showtime)
    {
        $showtime->load('theater');

        $bookedSeatIds = Booking::where('showtime_id', $showtime->id)
            ->pluck('seat_id');

        $availableSeats = Seat::where('theater_id', $showtime->theater_id)
            ->whereNotIn('seat_id', $bookedSeatIds)
            ->pluck('seat_id', 'seat_number'); // { "A1": 1, "A2": 2, ... }

        return response()->json([
            'available_seats' => $availableSeats
        ]);
    }

    // ✅ Store a single seat booking
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_id' => 'required|exists:seats,seat_id',
        ]);

        try {
            $booking = Booking::create([
                'user_id' => $validated['user_id'],
                'showtime_id' => $validated['showtime_id'],
                'seat_id' => $validated['seat_id'],
                'total_price' => 12, // static price or fetch dynamically
            ]);

            return response()->json([
                'booking_id' => $booking->id,
                'message' => 'Booking successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Booking failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Show booking details
    public function show(Booking $booking)
    {
        $booking->load(['showtime.movie', 'showtime.theater', 'seat']);

        return response()->json([
            'booking_id' => $booking->id,
            'movie_title' => $booking->showtime->movie->title,
            'theater_name' => $booking->showtime->theater->name,
            'show_date' => $booking->showtime->show_date,
            'show_time' => $booking->showtime->show_time,
            'total_price' => $booking->total_price,
            'seat' => [
                'seat_row' => $booking->seat->seat_row,
                'seat_number' => $booking->seat->seat_number,
            ],
        ]);
    }
}
