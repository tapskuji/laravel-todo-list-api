<?php

namespace App\Mail;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TodoReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $reminders;

    /**
     * Create a new message instance.
     *
     * @param array $reminders
     */
    public function __construct(array $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(env('ADMIN_EMAIL'))
            ->markdown('emails.todos.todo_reminder')
            ->with('reminders', $this->reminders);
    }
}
