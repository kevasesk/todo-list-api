<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            Task::factory()->count(5)->create(['user_id' => $user->id]);
        });
    }
}
