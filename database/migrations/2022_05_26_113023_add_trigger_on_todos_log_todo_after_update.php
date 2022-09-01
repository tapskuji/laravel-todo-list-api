<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTriggerOnTodosLogTodoAfterUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $queryDropTrigger = 'DROP TRIGGER IF EXISTS `log_todo_after_update`';
        $queryLogTodoAfterInsert = '
            CREATE TRIGGER log_todo_after_update
                AFTER UPDATE ON todos
            BEGIN
                INSERT INTO todo_logs (
                    old_id,
                    old_title,
                    old_description,
                    old_is_complete,
                    user_id,
                    old_due_date,
                    old_created_at,
                    old_updated_at
                )
                VALUES (
                    new.id,
                    new.title,
                    new.description,
                    new.is_complete,
                    new.user_id,
                    new.due_date,
                    new.created_at,
                    new.updated_at
                );
            END;
        ';
        DB::unprepared($queryDropTrigger);
        DB::unprepared($queryLogTodoAfterInsert);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $queryDropTrigger = 'DROP TRIGGER IF EXISTS `log_todo_after_update`';
        DB::unprepared($queryDropTrigger);
    }
}
