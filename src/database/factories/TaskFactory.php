<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\User;
use App\Enums\TaskStatus;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'status' => $this->faker->randomElement([TaskStatus::Todo, TaskStatus::Done]),
            'priority' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'created_at' => now(),
            'completed_at' => $this->faker->optional()->dateTimeThisYear,
        ];
    }
}
