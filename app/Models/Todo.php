<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'is_complete', 'due_date', 'user_id',
    ];

    /**
     * Gets the user that ones the todo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
