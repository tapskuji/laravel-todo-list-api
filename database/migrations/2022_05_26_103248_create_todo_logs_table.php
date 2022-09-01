<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodoLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('old_id');
            $table->string('old_title');
            $table->string('old_description')->nullable();
            $table->smallInteger('old_is_complete');
            $table->bigInteger('user_id');
            $table->dateTime('old_due_date');
            $table->dateTime('old_created_at');
            $table->dateTime('old_updated_at');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todo_logs');
    }
}
