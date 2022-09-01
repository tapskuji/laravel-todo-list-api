<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 'title', 'description', 'is_complete', 'due_date', 'user_id',
        return [
            'title' => $this->faker->title,
            'description' => $this->faker->sentence,
            'is_complete' => 0,
            'due_date' => Carbon::tomorrow()->toDateTimeString(),
            'user_id' => 1,
        ];
    }
}
