<?php

namespace Tests\Unit;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TodoLogsTest extends TestCase
{
    /* run migrations before test */
    use RefreshDatabase;

    /**
     * Test database schema.
     *
     * @return void
     */
    public function test_database_has_expected_table_todo_logs_and_columns()
    {
        $this->assertTrue(Schema::hasTable('todo_logs'), 'Todos logs table is missing');

        $this->assertTrue(
            Schema::hasColumns('todo_logs', [
                'id', 'old_id', 'old_title', 'old_description', 'old_is_complete', 'old_due_date', 'old_created_at',
                'old_updated_at', 'created_at'
            ]),
            'Todos logs table is missing some columns'
        );
    }

    public function test_log_entry_is_inserted_when_a_todo_is_created()
    {
        // Create a single App\Models\User instance...
        $user = User::factory()->create();
        // Create a single App\Models\Todo instance...
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $logs = DB::table('todo_logs')->count();

        $this->assertEquals(1, $logs);
    }

    public function test_log_entry_is_inserted_when_a_todo_is_updated()
    {
        // Create a single App\Models\User instance...
        $user = User::factory()->create();
        // Create a single App\Models\Todo instance...
        $todo = Todo::factory()->create(['user_id' => $user->id]);
        $todo->update(['title' => 'test']);
        $logs = DB::table('todo_logs')->count();

        $this->assertEquals(2, $logs);
    }
}
