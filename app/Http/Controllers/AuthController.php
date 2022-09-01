<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid request params',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // creating personal access token
        $token_name = env('API_DEFAULT_TOKEN_NAME');
        $token = $user->createToken($token_name);

        $response = [
            'message' => 'Registration successful',
            'data' => [
                $user,
                ['token' => $token->plainTextToken]
            ]
        ];

        return response()->json($response);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the user's current personal access token
        $request->user()->currentAccessToken()->delete();

        $response = [
            'message' => 'Logout successful',
            'data' => []
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $response = [
                'message' => 'Login failed',
                'errors' => [
                    'email_password' => [
                        'Invalid email or password'
                    ],
                ],
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($request->password, $user->password)) {
            $response = [
                'message' => 'Login failed',
                'errors' => [
                    'credentials' => [
                        'Invalid email or password'
                    ],
                ],
            ];
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        // creating personal access token
        $token_name = env('API_DEFAULT_TOKEN_NAME');
        $token = $user->createToken($token_name);

        $response = [
            'message' => 'Login successful',
            'data' => [
                ['token' => $token->plainTextToken]
            ]
        ];

        return response()->json($response);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $response = [
            'message' => 'User data',
            'data' => [
                $user,
            ]
        ];

        return response()->json($response);
    }
}
