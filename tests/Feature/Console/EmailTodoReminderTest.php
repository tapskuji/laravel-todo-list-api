<?php

namespace Tests\Feature\Console;

use App\Mail\TodoReminder;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTodoReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_is_sending_emails_for_incomplete_todos()
    {
        Mail::fake();

        $user = User::factory()->create();
        $todos = Todo::factory()->create([
            'user_id' => $user->id,
            'is_complete' => 0,
            'due_date' => Carbon::yesterday()->setTime(8, 0, 0)->toDateTimeString(),
        ]);

        $this->artisan('email:reminders');

        Mail::assertSent(TodoReminder::class);
    }

    public function test_command_is_sending_emails_for_incomplete_todos_from_previous_day_only()
    {
        Mail::fake();

        $user = User::factory()->create();
        $todos = Todo::factory()->create([
            'user_id' => $user->id,
            'is_complete' => 0,
            'due_date' => Carbon::yesterday()->subDays(2)->toDateTimeString(),
        ]);

        $this->artisan('email:reminders');

        Mail::assertNothingSent();
    }

    public function test_command_is_not_sending_emails_for_complete_todos()
    {
        Mail::fake();

        $user = User::factory()->create();
        $todos = Todo::factory()->create([
            'user_id' => $user->id,
            'is_complete' => 1,
            'due_date' => Carbon::yesterday()->toDateTimeString(),
        ]);

        $this->artisan('email:reminders');

        Mail::assertNothingSent();
    }
}
