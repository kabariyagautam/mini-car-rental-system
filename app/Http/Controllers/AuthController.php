<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role'     => ['required', Rule::in(['admin','supplier'])],
            ]);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            return response()->json([
                'user'    => $user,
                'status'  => true,
                'status_code' => 201,
                'message' => 'User registered successfully',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => $firstError,
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            // Validate input
            $data = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            // Fetch user
            $user = User::where('email', $data['email'])->first();

            // Invalid credentials
            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'status'      => false,
                    'status_code' => 401,
                    'message'     => 'Invalid credentials'
                ], 401);
            }

            // Create login token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'role'        => $user,
                'status'      => true,
                'status_code' => 200,
                'message'     => 'Login successful',
                'token'       => $token
            ], 200);

        }
        catch (\Illuminate\Validation\ValidationException $e) {

            // Get only the FIRST validation message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'status'      => false,
                'status_code' => 422,
                'message'     => $firstError,
            ], 422);
        }
        catch (\Exception $e) {

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => 'Something went wrong',
                'error'       => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status'      => true,
                'status_code' => 200,
                'message'     => 'Logged out successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'      => false,
                'message'     => 'Unable to logout',
                'error'       => $e->getMessage()
            ], 500);
        }
    }
}
