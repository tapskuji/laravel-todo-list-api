<?php

namespace App\Console\Commands;

use App\Mail\TodoReminder;
use App\Models\Todo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTodoReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email user when todo is incomplete and the due date has passed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isComplete = 0;
        $dateTime = Carbon::yesterday()->toDateString();
        $query = Todo::query()
            ->with(['user'])
            ->where('is_complete', $isComplete)
            ->whereDate('due_date', '=', $dateTime)
            ->orderBy('user_id');

        $sql = $query->toSql();
        Log::channel('email_error_logger')->info("Query for {$dateTime} : {$sql}");

        $todos = $query->get();
        $data = [];

        // organise the todos so that only 1 email is sent per user
        foreach ($todos as $todo) {
            $data[$todo->user_id][] = $todo;
        }

        // send emails
        foreach ($data as $user_id => $reminders) {
            $user = $reminders[0]->user;
            Log::channel('email_error_logger')->info("Sending email to {$user->email}");
            try {
                Mail::to($user)->send(new TodoReminder($reminders));
            } catch (\Exception $e) {
                Log::channel('email_error_logger')->warning("Failed to send email to {$user->email}");
                Log::channel('email_error_logger')->warning($e->getMessage());
            }
        }
        return 0;
    }
}
