<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'car_id'         => 'required|exists:cars,id',
                'customer_name'  => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'start_date'     => 'required|date|after_or_equal:today',
                'end_date'       => 'required|date|after_or_equal:start_date',
            ]);

            $car = Car::findOrFail($data['car_id']);

            if (! $car->approved) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => 'Car is not approved for booking'
                ], 422);
            }

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end   = Carbon::parse($data['end_date'])->endOfDay();

            // CHECK OVERLAP
            $overlap = Booking::where('car_id', $car->id)
                ->where(function($q) use ($start, $end) {
                    $q->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'status' => false,
                    'status_code' => 422,
                    'message' => 'Car not available for selected dates'
                ], 422);
            }

            $days = $start->diffInDays($end) + 1;
            $total = $days * $car->price_per_day;

            $booking = Booking::create([
                'car_id' => $car->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_price' => $total,
                'status' => 'confirmed',
            ]);

            return response()->json([
                'data' => new BookingResource($booking),
                'status' => true,
                'status_code' => 201,
                'message' => 'Booking created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // if you want an index for supplier to view bookings for his cars:
   public function supplierBookings(Request $request)
    {
        try {
            $user = $request->user();

            // Get bookings for cars that belong to this supplier
            $bookings = Booking::whereHas('car', function($q) use ($user) {
                $q->where('supplier_id', $user->id);
            })
            ->with('car') // eager load car details
            ->latest()
            ->get();

            return BookingResource::collection($bookings)
                ->additional([
                    'status'      => true,
                    'status_code' => 201,
                    'message'     => 'Bookings retrieved successfully',
                ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => 'Something went wrong',
                'error'       => $e->getMessage(),
            ], 500);
        }
    }

}
