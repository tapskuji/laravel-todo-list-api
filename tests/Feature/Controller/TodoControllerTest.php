<?php

namespace Tests\Feature\Controller;

use App\Models\Todo;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_all_todos()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson('/api/todos', $headers);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'message',
            'total',
            'data' => [
                '*' => ['id', 'title', 'description', 'is_complete', 'due_date', 'created_at', 'updated_at', 'user_id']
            ]
        ]);
    }

    public function test_authenticated_user_can_only_get_their_todos()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user2 = User::factory()->create();

        // create todo for user
        $user2Todos = Todo::factory()->count(2)->create(['user_id' => $user2->id]);
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];
        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson('/api/todos', $headers);
        $responseData = $response->json();

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(2, $responseData['total']);
    }

    public function test_authenticated_user_can_search_todo_using_keyword()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $title = 'A random title';
        $todo = Todo::factory()->create(['title' => $title, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $keyword = substr($title, 2, 6);

        // do some work
        $response = $this->getJson("/api/todos?keyword={$keyword}", $headers);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertStringContainsString($keyword, $response->getContent());
    }

    public function test_authenticated_user_can_search_todo_using_created_at()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $created_at = Carbon::now()->toDateTimeString();
        $todo = Todo::factory()->create(['created_at' => $created_at, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $created_at = Carbon::now()->toDateString();

        // do some work
        $response = $this->getJson("/api/todos?created_at={$created_at}", $headers);
        // make assertions
        $response->assertStatus(Response::HTTP_OK);
        $this->assertStringContainsString($created_at, $response->getContent());
    }

    public function test_validation_errors_on_attempt_to_search_todo_using_created_at()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $created_at = Carbon::now()->toDateTimeString();
        $todo = Todo::factory()->create(['created_at' => $created_at, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $created_at = "01-01-01";

        // do some work
        $response = $this->getJson("/api/todos?created_at={$created_at}", $headers);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString("created_at", $response->getContent());
    }

    public function test_authenticated_user_can_search_todo_using_updated_at()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $updated_at = Carbon::now()->toDateTimeString();
        $todo = Todo::factory()->create(['updated_at' => $updated_at, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $updated_at = Carbon::now()->toDateString();

        // do some work
        $response = $this->getJson("/api/todos?updated_at={$updated_at}", $headers);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertStringContainsString($updated_at, $response->getContent());
    }

    public function test_validation_errors_on_attempt_to_search_todo_using_updated_at()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $updated_at = Carbon::now()->toDateTimeString();
        $todo = Todo::factory()->create(['updated_at' => $updated_at, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $updated_at = "01-01-01";

        // do some work
        $response = $this->getJson("/api/todos?updated_at={$updated_at}", $headers);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString("updated_at", $response->getContent());
    }

    public function test_authenticated_user_can_search_todo_using_due_date()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $due_date = Carbon::tomorrow()->toDateTimeString();
        $todo = Todo::factory()->create(['due_date' => $due_date, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $due_date = Carbon::tomorrow()->toDateString();

        // do some work
        $response = $this->getJson("/api/todos?due_date={$due_date}", $headers);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertStringContainsString($due_date, $response->getContent());
    }

    public function test_validation_errors_on_attempt_to_search_todo_using_due_date()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $due_date = Carbon::now()->toDateTimeString();
        $todo = Todo::factory()->create(['due_date' => $due_date, 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $due_date = "01-01-01";

        // do some work
        $response = $this->getJson("/api/todos?due_date={$due_date}", $headers);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertStringContainsString("due_date", $response->getContent());
    }

    public function test_authenticated_user_can_request_sorted_search_response()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todos for user
        $todo1 = Todo::factory()->create(['title' => 'b', 'user_id' => $user->id]);
        $todo2 = Todo::factory()->create(['title' => 'c', 'user_id' => $user->id]);
        $todo3 = Todo::factory()->create(['title' => 'a', 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $sort_by = 'title';

        // do some work
        $response = $this->getJson("/api/todos?sort_by={$sort_by}", $headers);
        $responseData = $response->json();
        $titles = '';
        foreach ($responseData['data'] as $index => $todo) {
            $titles .= $todo['title'];
        }

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals('abc', $titles);
    }

    public function test_authenticated_user_can_request_descending_sorted_search_response()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todos for user
        $todo1 = Todo::factory()->create(['title' => 'b', 'user_id' => $user->id]);
        $todo2 = Todo::factory()->create(['title' => 'c', 'user_id' => $user->id]);
        $todo3 = Todo::factory()->create(['title' => 'a', 'user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $sort_order = 'desc';

        // do some work
        $response = $this->getJson("/api/todos?sort_order={$sort_order}", $headers);
        $responseData = $response->json();
        $ids = '';
        foreach ($responseData['data'] as $index => $todo) {
            $ids .= $todo['id'];
        }

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals('321', $ids);
    }

    public function test_authenticated_user_can_create_a_todo()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'title' => 'Test title',
            'description' => 'Test description',
            'is_complete' => 0,
            'due_date' => Carbon::tomorrow()->toDateTimeString(),
        ];

        // do some work
        $response = $this->postJson('/api/todos', $requestParameters, $headers);
        $responseData = $response->json();

        // make assertions
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'message',
            'total',
            'data' => [
                '*' => ['id', 'title', 'description', 'is_complete', 'due_date', 'created_at', 'updated_at', 'user_id']
            ]
        ]);
        $this->assertSame($requestParameters['title'], $responseData['data'][0]['title']);
        $this->assertSame($requestParameters['description'], $responseData['data'][0]['description']);
        $this->assertSame($requestParameters['is_complete'], $responseData['data'][0]['is_complete']);
        $this->assertSame($user->id, $responseData['data'][0]['user_id']);
    }

    public function test_validation_errors_when_attempting_to_create_a_todo()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->postJson('/api/todos', [], $headers);
        $responseData = $response->json();

        // make assertions
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonStructure([
            'message',
            'errors' => ['title', 'description', 'is_complete', 'due_date']
        ]);
        $this->assertSame('Invalid request params', $responseData['message']);
    }

    public function test_authenticated_user_can_get_a_todo_using_id()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/todos/{$todos[1]->id}", $headers);
        $responseData = $response->json();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'message',
            'total',
            'data' => [
                '*' => ['id', 'title', 'description', 'is_complete', 'due_date', 'created_at', 'updated_at', 'user_id']
            ]
        ]);

        $this->assertEquals(1, $responseData['total']);
        $this->assertSame($todos[1]->title, $responseData['data'][0]['title']);
    }

    public function test_not_found_http_response_returned_when_requesting_todo_that_does_not_exist()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/todos/3", $headers);
        $responseData = $response->json();

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_forbidden_http_response_returned_when_requesting_todo_that_does_not_belong_to_them()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $user2 = User::factory()->create();

        // create todo for user 2
        $todos = Todo::factory()->count(2)->create(['user_id' => $user2->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/todos/{$todos[1]->id}", $headers);
        $responseData = $response->json();

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_authenticated_user_can_update_a_todo()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'title' => 'Test title',
            'description' => 'Test description',
            'is_complete' => 1,
            'due_date' => Carbon::tomorrow()->addDay()->toDateTimeString(),
        ];

        // do some work
        $response = $this->putJson("/api/todos/{$todos[0]->id}", $requestParameters, $headers);
        $responseData = $response->json();

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'message',
            'total',
            'data' => [
                '*' => ['id', 'title', 'description', 'is_complete', 'due_date', 'created_at', 'updated_at', 'user_id']
            ]
        ]);

        $this->assertEquals(1, $responseData['total']);
        $this->assertSame($requestParameters['title'], $responseData['data'][0]['title']);
        $this->assertSame($requestParameters['description'], $responseData['data'][0]['description']);
        $this->assertSame($requestParameters['is_complete'], $responseData['data'][0]['is_complete']);
        $this->assertSame($requestParameters['due_date'], $responseData['data'][0]['due_date']);
    }

    public function test_validation_errors_when_attempting_to_update_a_todo()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'title' => '',
            'is_complete' => false,
            'due_date' => Carbon::yesterday()->toDateTimeString(),
        ];

        // do some work
        $response = $this->putJson("/api/todos/{$todos[0]->id}", $requestParameters, $headers);
        $responseData = $response->json();


        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonStructure([
            'message',
            'errors' => ['title', 'is_complete', 'due_date']
        ]);
    }

    public function test_http_error_response_when_attempting_to_update_a_todo_without_specifying_fields()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [];

        // do some work
        $response = $this->putJson("/api/todos/{$todos[0]->id}", $requestParameters, $headers);
        $responseData = $response->json();


        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_not_found_http_response_returned_when_requesting_to_update_a_todo_that_does_not_exist()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'title' => 'New title',
        ];

        // do some work
        $response = $this->putJson("/api/todos/3", $requestParameters, $headers);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_forbidden_http_response_returned_when_attempting_to_update_a_todo_that_does_not_belong_to_them()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);
        $user2 = User::factory()->create();

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user2->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'title' => 'New title',
        ];

        // do some work
        $response = $this->putJson("/api/todos/{$todos[0]->id}", $requestParameters, $headers);
        $responseData = $response->json();


        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_authenticated_user_can_delete_a_todo()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->deleteJson("/api/todos/{$todos[0]->id}", [], $headers);
        $todo = Todo::query()->where('id', $todos[0]->id)->get();

        // make assertions
        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue($todo->isEmpty());
    }

    public function test_not_found_http_response_returned_when_requesting_to_delete_a_todo_that_does_not_exist()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->deleteJson("/api/todos/3", [], $headers);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_forbidden_http_response_returned_when_attempting_to_delete_a_todo_that_does_not_belong_to_them()
    {
        // create user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);
        $user2 = User::factory()->create();

        // create todo for user
        $todos = Todo::factory()->count(2)->create(['user_id' => $user2->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->deleteJson("/api/todos/{$todos[0]->id}", [], $headers);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
