<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    public function index(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $todo_logs = DB::table('todo_logs')->where('old_id', '=', $id)->where('user_id', '=', $user->id)->get();

        if ($todo_logs->isEmpty()) {
            return response()->json([
                'message' => 'Not found',
                'errors' => [
                    'id' => "Requested resource with id {$id} not found",
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'successful',
            'total' => count($todo_logs),
            'data' => $todo_logs
        ]);
    }
}
