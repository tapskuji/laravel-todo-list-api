<?php

namespace Tests\Unit;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Class UserTest
 * Tests for the user model and schema
 * @package Tests\Unit
 */
class UserTest extends TestCase
{
    /* run migrations before test */
    use RefreshDatabase;

    /**
     * Test user model's database schema.
     *
     * @return void
     */
    public function test_database_has_expected_table_users_and_columns()
    {
        $this->assertTrue(Schema::hasTable('users'), 'Users table is missing');

        $this->assertTrue(
            Schema::hasColumns('users', [
                'id','name', 'email', 'email_verified_at', 'password', 'profile_photo'
            ]),
            'Users table is missing some columns'
        );
    }

    public function test_user_is_only_created_when_all_required_fields_are_provided()
    {
        $expectedUserModelFields = [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => '123',
        ];

        $time = now();
        $user = User::create($expectedUserModelFields);
        $databaseUser = $user->makeVisible(['password'])->makeHidden(['id','created_at','updated_at', 'profile_image_url'])->toArray();

        $this->assertCount(1, User::all());
        $this->assertSame($expectedUserModelFields, $databaseUser);
        // time based assertions will depend on how fast the computer is, a 10th of a second should be fine for testing
        $this->assertEqualsWithDelta($time, $user->created_at, 1, 'User created_at time not accurate');
        $this->assertEqualsWithDelta($time, $user->updated_at, 1, 'User updated_at time not accurate');
        $this->assertNull($user->profile_photo);
    }

    public function test_user_email_is_stored_in_lower_case()
    {
        $email = 'TEST@EXAMPLE.COM';

        $user = User::create([
            'name' => 'Test',
            'email' => $email,
            'password' => '123',
        ]);

        $this->assertEquals(strtolower($email), $user->email);
    }

    public function test_user_model_default_profile_image_url()
    {
        $name = 'John Doe';
        $user = User::create([
            'name' => $name,
            'email' => 'test@example.com',
            'password' => '123',
        ]);
        // test for attribute added to user model
        $expectedProfileImageUrl = 'https://ui-avatars.com/api/?name=' . urlencode($name);
        $this->assertEquals($expectedProfileImageUrl, $user->profile_image_url);
    }

    public function test_an_exception_is_thrown_for_duplicate_user_email()
    {
        $email = 'test@example.com';

        $this->expectException(\Exception::class);

        $user1 = User::factory()->count(1)->create([
            'email' => $email
        ]);

        $user2 = User::create([
            'name' => 'Test',
            'email' => $email
        ]);
    }

    public function test_a_user_has_zero_todos()
    {
        $users = User::factory()
            ->count(1)
            ->create();
        $user = $users[0];
        $user->load(['todos']);
        $todos = $user->todos();
        $this->assertEquals(0, $todos->count());
    }

    public function test_a_user_has_many_todos()
    {
        $expectedCount = 3;

        $users = User::factory()
            ->count(1)
            ->has(Todo::factory()->count($expectedCount))
            ->create();

        $user = $users[0];
        $user->load(['todos']);
        $todos = $user->todos();

        $this->assertEquals($expectedCount, $todos->count());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $todos);
    }
}
