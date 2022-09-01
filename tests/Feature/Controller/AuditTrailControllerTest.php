<?php

namespace Tests\Feature\Controller;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuditTrailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_todo_logs_for_an_existing_todo()
    {
        // create user and todos
        $password = '1234567890';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData['data'][0]);
        $token = $responseData['data'][0]['token'];
        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/audit-trail/{$todo->id}", $headers);
        $responseData = $response->json();

        // make assertions
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(1, $responseData['total']);
    }

    public function test_user_cannot_get_todo_logs_for_an_todo_that_does_not_exist()
    {
        // create user and todos
        $password = '1234567890';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData['data'][0]);
        $token = $responseData['data'][0]['token'];
        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/audit-trail/2", $headers);
        $responseData = $response->json();

        // make assertions
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_user_cannot_get_todo_logs_for_an_todo_that_is_not_owned_by_them()
    {
        // create user and todos
        $password = '1234567890';
        $user = User::factory()->create(['password' => bcrypt($password)]);
        $user2 = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todoUser2 = Todo::factory()->create(['user_id' => $user2->id]);

        // login and get token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $responseData = $response->json();
        $this->assertArrayHasKey('token', $responseData['data'][0]);
        $token = $responseData['data'][0]['token'];
        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $response = $this->getJson("/api/audit-trail/{$todoUser2->id}", $headers);
        $responseData = $response->json();

        // make assertions
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
