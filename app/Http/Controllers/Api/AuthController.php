<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    /**
     * get token
     *
     * Authenticate user and generate API token
     *
     * @param  string  $email  The email address of the user
     * @param  string  $password  The password of the user
     * @param  string  $device_name  The name of the device requesting the token
     */
    public function generateToken(Request $request)
    {
        ray($request->all());
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        ray($user);

        if ( ! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete existing tokens for this device name
        $user->tokens()->where('name', $request->device_name)->delete();

        // Create token directly without forceFill
        $token = $user->createToken($request->device_name)->plainTextToken;
        ray($token);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * logout
     *
     * @param  string  $token  The token to be revoked
     */
    public function logout(Request $request)
    {
        // Only revoke the current token instead of all tokens
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked successfully'], 200);
    }

    /**
     * Reset/Refresh the current token
     *
     * @param  string  $token  The token to be revoked
     */
    public function resetToken(Request $request)
    {
        $user = $request->user();
        $deviceName = $request->user()->currentAccessToken()->name;

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        // Generate new token with same device name
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}
