<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Supplier\CarController as SupplierCarController;
use Illuminate\Support\Facades\Route;

// public
Route::post('register', [AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);

// bookings (customer)
Route::post('bookings', [BookingController::class,'store']);

// auth required
Route::middleware(['auth:sanctum'])->group(function() {

    Route::post('logout', [AuthController::class,'logout']);

    // admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function() {
        Route::get('dashboard', [AdminController::class,'dashboard']);
        Route::get('suppliers', [AdminController::class,'suppliersIndex']);
        Route::get('suppliers/{id}', [AdminController::class,'supplierShow']);
        Route::delete('suppliers/{id}', [AdminController::class,'supplierDestroy']);
        Route::post('cars/{id}/approve', [AdminController::class,'approveCar']);
        Route::get('bookings', [AdminController::class,'bookings']);
    });

    // supplier routes
    Route::prefix('supplier')->middleware('role:supplier')->group(function() {

        Route::get('cars', [SupplierCarController::class, 'index'])->name('supplier.cars.index');
        Route::post('cars', [SupplierCarController::class, 'store'])->name('supplier.cars.store');
        Route::get('cars/{id}', [SupplierCarController::class, 'show'])->name('supplier.cars.show');
        Route::post('cars/{id}', [SupplierCarController::class, 'update'])->name('supplier.cars.update');
        Route::delete('cars/{id}', [SupplierCarController::class, 'destroy'])->name('supplier.cars.destroy');

        Route::get('bookings', [BookingController::class,'supplierBookings']);
        // For availability, add controller endpoints as needed
    });

});
