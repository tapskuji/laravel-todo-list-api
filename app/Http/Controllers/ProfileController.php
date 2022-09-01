<?php

namespace App\Http\Controllers;

use App\Services\Base64ToImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed|min:6|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid request params',
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        if (!Hash::check($request->old_password, $user->password)) {
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

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $response = [
            'message' => 'Password update successful',
            'data' => [
                $user,
            ]
        ];

        return response()->json($response);
    }

    public function update(Request $request, Base64ToImageService $imageService): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $data = [];

        if ($request->has('name')) {
            $validator = Validator::make($request->all(), [
                'name' => 'string|min:2|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid request params',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $data['name'] = $request->name;
        }

        if ($request->has('profile_photo')) {
            $validator = Validator::make($request->all(), [
                'profile_photo' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid request params',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            try {
                $imagePath = public_path() . '/uploads/profile_photos/';
                $data['profile_photo'] = $imageService->saveToDrive($imagePath, $request->profile_photo, $user->profile_photo);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed',
                    'errors' => [
                        'image_format' => [
                            $e->getMessage()
                        ],
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (empty($data)) {
            $response = [
                'message' => 'Profile update failed',
                'errors' => [
                    'profile' => [
                        "No data to update"
                    ],
                ],
            ];
            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $user->update($data);

        return response()->json([
            'message' => 'successful',
            'total' => 1,
            'data' => [
                $user
            ]
        ]);
    }
}
