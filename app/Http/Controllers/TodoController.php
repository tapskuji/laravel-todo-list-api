<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /* we will only use cache when user requests all the todos */
        if (empty($request->all())) {
            $cacheKey = $this->getUserBasedCacheKey($user->id);
            $tomorrow = Carbon::tomorrow();
            $seconds = Carbon::now()->diffInSeconds($tomorrow);
            $todos = Cache::remember($cacheKey, $seconds, function () use ($user) {
                return Todo::where('user_id', $user->id)->get();
            });

            return response()->json($this->responseArray($todos));
        }

        $query = Todo::query()
            ->where('user_id', $user->id);

        if ($request->has('keyword')) {
            $query->where('title', 'LIKE', '%' . $request->keyword . '%')
                ->orWhere('description', 'LIKE', '%' . $request->keyword . '%');
        }

        $isStrict = boolval($request->date_strict);
        $operator = $isStrict ? '=' : '>=';

        if ($request->has('created_at')) {
            $validator = Validator::make($request->all(), [
                'created_at' => 'date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->errorResponseArray('Invalid request params', $validator->errors()),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $query->where('created_at', $operator, $request->created_at);
        }

        if ($request->has('updated_at')) {
            $validator = Validator::make($request->all(), [
                'updated_at' => 'date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->errorResponseArray('Invalid request params', $validator->errors()),
                    Response::HTTP_BAD_REQUEST
                );
            }
            $query->where('updated_at', $operator, $request->updated_at);
        }

        if ($request->has('due_date')) {
            $validator = Validator::make($request->all(), [
                'due_date' => 'date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $this->errorResponseArray('Invalid request params', $validator->errors()),
                    Response::HTTP_BAD_REQUEST
                );
            }
            $query->where('due_date', $operator, $request->due_date);
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['id', 'title', 'due_date', 'created_at', 'updated_at'])) {
            $sortBy = $request->sort_by;
        } else {
            $sortBy = 'id';
        }

        if ($request->has('sort_order') && in_array($request->sort_order, ['asc', 'desc'])) {
            $sortOrder = $request->sort_order;
        } else {
            $sortOrder = 'asc';
        }

        $todos = $query->orderBy($sortBy, $sortOrder)->get();

        return response()->json($this->responseArray($todos));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2|max:100',
            'description' => 'required',
            'is_complete' => 'required|integer|between:0,1',
            'due_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(
                $this->errorResponseArray('Invalid request params', $validator->errors()),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $request->user();

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
            'is_complete' => $request->is_complete,
            'due_date' => $request->due_date,
            'user_id' => $user->id,
        ]);

        Cache::forget($this->getUserBasedCacheKey($request->user()->id));

        return response()->json($this->responseArray([$todo]), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $todo = Todo::find($id);

        if (!$todo) {
            return response()->json($this->errorResponseArray('Not found', [
                'id' => [
                    "Requested resource with id {$id} not found"
                ],
            ]), Response::HTTP_NOT_FOUND);
        }

        if ($todo->user_id != $request->user()->id) {
            return response()->json($this->errorResponseArray('Access denied'), Response::HTTP_FORBIDDEN);
        }

        return response()->json($this->responseArray([$todo]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $updateFields = [];
        $errors = [];

        if ($request->has('title')) {

            $validator = Validator::make($request->only('title'), ['title' => 'filled|string|min:1|max:100']);
            if ($validator->fails()) {
                $errors['title'] = $validator->errors()->get('title');;
            }
            $updateFields[] = 'title';
        }

        // $request->description will not need any validation
        if ($request->has('description')) {
            $updateFields[] = 'description';
        }

        if ($request->has('is_complete')) {
            $validator = Validator::make($request->only('is_complete'), ['is_complete' => 'filled|integer|between:0,1']);
            if ($validator->fails()) {
                $errors['is_complete'] = $validator->errors()->get('is_complete');
            }
            $updateFields[] = 'is_complete';
        }

        if ($request->has('due_date')) {
            $validator = Validator::make($request->only('due_date'), ['due_date' => 'filled|date_format:Y-m-d H:i:s|after_or_equal:today']);
            if ($validator->fails()) {
                $errors['due_date'] = $validator->errors()->get('due_date');
            }
            $updateFields[] = 'due_date';
        }

        if ($errors) {
            return response()->json(
                $this->errorResponseArray('Invalid request params', $errors),
                Response::HTTP_BAD_REQUEST
            );
        }

        $todo = Todo::find($id);

        if (!$todo) {
            return response()->json($this->errorResponseArray('Not found', [
                'id' => [
                    "Requested resource with id {$id} not found"
                ],
            ]), Response::HTTP_NOT_FOUND);
        }


        if ($todo->user_id != $request->user()->id) {
            return response()->json($this->errorResponseArray('Access denied'), Response::HTTP_FORBIDDEN);
        }

        if (empty($updateFields)) {
            return response()->json($this->errorResponseArray('Update failed', [
                'profile' => [
                    "No data to update"
                ],
            ]), Response::HTTP_BAD_REQUEST);
        }

        $todo->update($request->only($updateFields));

        Cache::forget($this->getUserBasedCacheKey($request->user()->id));

        return response()->json($this->responseArray([$todo]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request,int $id): JsonResponse
    {
        $todo = Todo::find($id);

        if (!$todo) {
            return response()->json($this->errorResponseArray('Not found', [
                'id' => [
                    "Requested resource with id {$id} not found"
                ],
            ]), Response::HTTP_NOT_FOUND);
        }

        if ($todo->user_id != $request->user()->id) {
            return response()->json($this->errorResponseArray('Access denied'), Response::HTTP_FORBIDDEN);
        }

        $todo->delete();

        Cache::forget($this->getUserBasedCacheKey($request->user()->id));

        return response()->json($this->responseArray([]));
    }

    public function responseArray($data, $message = 'successful'): array
    {
        return [
            'message' => $message,
            'total' => count($data),
            'data' => $data
        ];
    }

    public function errorResponseArray($message, $errors = []): array
    {
        return [
            'message' => $message,
            'errors' => $errors,
        ];
    }

    private function getUserBasedCacheKey($id): string
    {
        return 'todos_user_' . $id;

    }
}
