<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'You are not authorized to view cars'
                ], 403);
            }

            $cars = Car::where('supplier_id', $user->id)->get();

            foreach ($cars as $car) {
                if ($car->supplier_id != $user->id) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 403,
                        'message' => 'You are not authorized to view this car'
                    ], 403);
                }
            }

            return CarResource::collection($cars)
                ->additional([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Cars Retrieved Successfully',
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'name'          => 'required|string|max:255',
                'type'          => 'required|string|max:255',
                'location'      => 'required|string|max:255',
                'price_per_day' => 'required|numeric|min:0',
                'image'         => 'nullable|image|max:2048',
            ]);

            $data['supplier_id'] = $request->user()->id;
            $data['approved'] = false;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('cars', 'public');
            }

            $car = Car::create($data);

            return (new CarResource($car))
                ->additional([
                    'status' => true,
                    'status_code' => 201,
                    'message' => 'Car Created Successfully'
                ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return first validation error
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => $e->errors() ? array_values($e->errors())[0][0] : 'Validation failed',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {

            $car = Car::find($id);

            if (!$car) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Car not found'
                ], 404);
            }

            // 🔐 Supplier authorization check (same as destroy)
            if ($request->user()->id != $car->supplier_id) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'You are not authorized to view this car'
                ], 403);
            }

            return (new CarResource($car))
                ->additional([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Car Retrieved Successfully'
                ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $car = Car::find($id);

            if (!$car) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Car not found'
                ], 404);
            }

            if ($request->user()->id != $car->supplier_id) {
                return response()->json([
                    'status' => false,
                    'status_code'=> 403,
                    'message' => 'You are not authorized to update this car'
                ], 403);
            }

            $car->name = $request->name ?? $car->name;
            $car->type = $request->type ?? $car->type;
            $car->location = $request->location ?? $car->location;
            $car->price_per_day = $request->price_per_day ?? $car->price_per_day;

            if ($request->hasFile('image')) {
                if ($car->image) {
                    Storage::disk('public')->delete($car->image);
                }
                $path = $request->file('image')->store('cars', 'public');
                $car->image = $path;
            }

            $car->save();

             return (new CarResource($car))
                ->additional([
                'status' => true,
                'status_code' => 201,
                'message' => 'Car updated successfully'
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

    public function destroy(Request $request, $id)
    {
        try {

            $car = Car::find($id);

            if (!$car) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Car not found'
                ], 404);
            }

            if ($request->user()->id != $car->supplier_id) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'You are not authorized to delete this car'
                ], 403);
            }

            if ($car->image && Storage::disk('public')->exists($car->image)) {
                Storage::disk('public')->delete($car->image);
            }

            $car->delete();

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Car deleted successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
