<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $faker = Factory::create();
        $password = $faker->password(6);

        $response = $this->post('/api/register', [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
        $response->assertStatus(200);
    }

    public function test_user_validation_errors_on_register()
    {
        $expected = '{"message":"Invalid request params","errors":{"name":["The name field is required."],"email":["The email field is required."],"password":["The password field is required."]}}';
//        $faker = Factory::create();
//        $password = $faker->password(6);

//        $response = $this->post('/api/register', [
//            'email' => $faker->email,
//            'password' => $password,
//            'password_confirmation' => $password,
//        ]);

        $response = $this->post('/api/register');

        // make assertions
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function test_authenticated_user_can_logout_with_token()
    {
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $responseLogin = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $jsonData = $responseLogin->json();
        $this->assertArrayHasKey('token', $jsonData['data'][0]);
        $token = $jsonData['data'][0]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $responseLogout = $this->postJson('/api/logout', [], $headers);
        $responseLogout->assertStatus(Response::HTTP_OK);

    }

    public function test_user_can_login_with_correct_credentials_and_receives_auth_token()
    {
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $content =  $response->getContent();
        $jsonData = json_decode($content);
        $token = $jsonData->data[0]->token;

        $response->assertStatus(Response::HTTP_OK);
        $this->assertIsString($token);
    }

    public function test_user_cannot_login_with_incorrect_email_credential()
    {
        $expected = '{"message":"Login failed","errors":{"email_password":["Invalid email or password"]}}';

        // created user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // run test
        $response = $this->postJson('/api/login', [
            'email' => 'test@test.test',
            'password' => '1234567890',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function test_user_cannot_login_with_incorrect_password_credential()
    {
        $expected = '{"message":"Login failed","errors":{"credentials":["Invalid email or password"]}}';

        // created user
        $password = '1234567890';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        // run test
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect_password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function test_401_is_returned_for_user_request_without_authenticating()
    {
        $user = User::factory()->create();
        $response = $this->getJson('/api/user');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_404_is_returned_for_user_request_without_authenticating()
    {
        $faker = Factory::create();
        $password = $faker->password(6);

        $response = $this->post('/api/register', [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][1]);
        $token = $jsonData['data'][1]['token'];

        $headers = ['Authorization' => 'Bearer ' . $token];
        $responseGetUser = $this->getJson('/api/user', $headers);
        $responseGetUser->assertStatus(Response::HTTP_OK);
        $this->assertArrayHasKey('data', $responseGetUser->json());
    }
}
