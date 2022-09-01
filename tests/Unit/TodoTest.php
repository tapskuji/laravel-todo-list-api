<?php

namespace Tests\Unit;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Class TodoTest
 * Tests for the todo model and schema
 * @package Tests\Unit
 */
class TodoTest extends TestCase
{
    /* run migrations before test */
    use RefreshDatabase;

    /**
     * Test database schema.
     *
     * @return void
     */
    public function test_database_has_expected_table_todos_and_columns()
    {
        $this->assertTrue(Schema::hasTable('todos'), 'Todos table is missing');

        $this->assertTrue(
            Schema::hasColumns('todos', [
                'id', 'title', 'description', 'is_complete', 'due_date', 'user_id',
            ]),
            'Todos table is missing some columns'
        );
    }

    public function test_a_todo_belongs_to_a_user()
    {
        // Create a single App\Models\User instance...
        $user = User::factory()->create();
        // Create a single App\Models\Todo instance...
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $todo->user);
    }

}
