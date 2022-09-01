<?php

namespace Tests\Feature\Controller;

use App\Services\Base64ToImageService;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_password()
    {
        //$this->withoutExceptionHandling();
        $faker = Factory::create();
        $password = $faker->password(6);

        $response = $this->postJson('/api/register', [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][1]);
        $token = $jsonData['data'][1]['token'];

        $passwordNew = '123456789';
        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'old_password' => $password,
            'password' => $passwordNew,
            'password_confirmation' => $passwordNew,
        ];
        $responsePasswordChange = $this->putJson('/api/profile/change-password', $requestParameters, $headers);

        // make assertions
        $responsePasswordChange->assertStatus(Response::HTTP_OK);
        $this->assertArrayHasKey('data', $responsePasswordChange->json());
    }

    public function test_validation_errors_when_attempting_to_change_password()
    {
        //$this->withoutExceptionHandling();
        $expected = '{"message":"Invalid request params","errors":{"old_password":["The old password field is required."],"password":["The password field is required."]}}';
        $token = $this->registerFakeUser();
        $headers = ['Authorization' => 'Bearer ' . $token];

        // do some work
        $responsePasswordChange = $this->putJson('/api/profile/change-password', [], $headers);

        // make assertions
        $responsePasswordChange->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString($expected, $responsePasswordChange->getContent());
    }

    public function test_authenticated_user_cannot_change_password_with_incorrect_credentials()
    {
        // setup
        //$this->withoutExceptionHandling();
        $token = $this->registerFakeUser();

        $passwordIncorrect = '123456789';
        $passwordNew = '123456789';
        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'old_password' => $passwordIncorrect,
            'password' => $passwordNew,
            'password_confirmation' => $passwordNew,
        ];

        // do some work
        $responsePasswordChange = $this->putJson('/api/profile/change-password', $requestParameters, $headers);

        // make assertions
        $responsePasswordChange->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_authenticated_user_cannot_update_email_and_password()
    {
        $token = $this->registerFakeUser();

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'email' => 'test@email.com',
            'password' => 'password',
        ];

        $responseUpdateProfile = $this->putJson('/api/profile', $requestParameters, $headers);
        // make assertions
        $responseUpdateProfile->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_authenticated_user_can_update_name()
    {
        $token = $this->registerFakeUser();

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'name' => 'Test name',
        ];

        $responseUpdateProfile = $this->putJson('/api/profile', $requestParameters, $headers);
        // make assertions
        $responseUpdateProfile->assertStatus(Response::HTTP_OK);
    }

    public function test_validation_errors_when_attempting_to_update_name()
    {
        //$this->withoutExceptionHandling();
        $expected = '{"message":"Invalid request params","errors":{"name":["The name must be a string.","The name must be at least 2 characters."]}}';
        $token = $this->registerFakeUser();
        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = ['name' => ''];

        // do some work
        $response = $this->putJson('/api/profile', $requestParameters, $headers);

        // make assertions
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function test_validation_errors_when_attempting_to_update_profile_photo()
    {
        //$this->withoutExceptionHandling();
        $expected = '{"message":"Invalid request params","errors":{"profile_photo":["The profile photo must be a string."]}}';
        $token = $this->registerFakeUser();
        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = ['profile_photo' => ''];
        // do some work
        $response = $this->putJson('/api/profile', $requestParameters, $headers);

        // make assertions
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

        // test base64 format
        $expected = '{"message":"Image upload failed","errors":{"image_format":["Invalid data URL format. Expected data:image\/png;base64, or data:image\/jpg;base64,"]}}';
        $requestParameters = ['profile_photo' => '123'];

        // do some work
        $response = $this->putJson('/api/profile', $requestParameters, $headers);

        // make assertions
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function test_authenticated_user_can_update_profile_photo()
    {
        //$this->withoutExceptionHandling();

        $this->mock(Base64ToImageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('saveToDrive')->once()->andReturn('fake-image.jpg');
        });

        $token = $this->registerFakeUser();

        $headers = ['Authorization' => 'Bearer ' . $token];
        $requestParameters = [
            'profile_photo' => 'data:image/jpg;base64,abcd',
        ];

        $responseUpdateProfile = $this->putJson('/api/profile', $requestParameters, $headers);
        // make assertions
        $responseUpdateProfile->assertStatus(Response::HTTP_OK);
    }

    public function registerFakeUser()
    {
        $faker = Factory::create();
        $password = $faker->password(6);

        $response = $this->postJson('/api/register', [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $jsonData = $response->json();
        $this->assertArrayHasKey('token', $jsonData['data'][1]);
        return $jsonData['data'][1]['token'];
    }
}
