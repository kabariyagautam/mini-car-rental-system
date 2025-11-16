<?php

namespace App\Http\Controllers;

use App\Http\Resources\CarResource;
use App\Http\Resources\BookingResource;
use App\Models\Car;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // -------------------------
    // DASHBOARD
    // -------------------------
    public function dashboard()
    {
        try {
            $totals = [
                'cars'      => Car::count(),
                'bookings'  => Booking::count(),
                'suppliers' => User::where('role', 'supplier')->count(),
            ];

            return response()->json([
                'data'    => $totals,
                'status'  => true,
                'status_code'  => 201,
                'message' => 'Dashboard fetched successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------
    // SUPPLIERS LIST
    // -------------------------
    public function suppliersIndex()
    {
        try {
            $suppliers = User::where('role', 'supplier')->get();

            return response()->json([
                'data'    => $suppliers,
                'status'  => true,
                'status_code'  => 201,
                'message' => 'Supplier list fetched',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------
    // VIEW SUPPLIER
    // -------------------------
    public function supplierShow($id)
    {
        try {
            $supplier = User::where('role', 'supplier')->findOrFail($id);

            return response()->json([
                'data'    => $supplier,
                'status'  => true,
                'message' => 'Supplier details fetched'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 404,
                'message' => 'Supplier not found'
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------
    // DELETE SUPPLIER
    // -------------------------
    public function supplierDestroy($id)
    {
        try {
            $supplier = User::where('role', 'supplier')->findOrFail($id);
            $supplier->delete();

            return response()->json([
                'status'  => true,
                'status_code'  => 201,
                'message' => 'Supplier deleted successfully'
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 404,
                'message' => 'Supplier not found'
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------
    // APPROVE CAR
    // -------------------------
    public function approveCar(Car $car, Request $request)
    {
        try {
            $car->approved = $request->input('approved', true);
            $car->save();

            return response()->json([
                'data'    => new CarResource($car),
                'status'  => true,
                'status_code'  => 201,
                'message' => 'Car approved successfully'
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------
    // ALL BOOKINGS
    // -------------------------
    public function bookings()
    {
        try {
            $bookings = Booking::with('car')->latest()->get();

            return response()->json([
                'data'    => BookingResource::collection($bookings),
                'status'  => true,
                'status_code'  => 201,
                'message' => 'Bookings list fetched'
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'status_code'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
