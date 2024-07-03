<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/auth/register",
     * summary="Register",
     * description="Register by providing details.",
     * operationId="authRegister",
     * tags={"auth"},
     *
     * @OA\RequestBody(
     *    required=true,
     *    description="Registration details.",
     *
     *    @OA\JsonContent(
     *       required={"name","email","password"},
     *
     *       @OA\Property(property="name", type="string", format="name", example="Marie"),
     *       @OA\Property(property="email", type="string", format="email", example="marie.warren@email.com"),
     *       @OA\Property(property="password", type="string", format="password", example="secret1234"),
     *    ),
     * ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Successful Operation",
     *
     *    @OA\JsonContent()
     *    ),
     *
     * @OA\Response(
     *    response=422,
     *    description="Unprocessable Content"
     * )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $message = 'Successfully created user.';

        if (Features::enabled(Features::emailVerification())) {
            $user->sendEmailVerificationNotification();
            $message = $message.' Please verify your email address by clicking on the link we just emailed to you.';
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $message,
            'token' => $token,
        ],
            201);
    }
}
