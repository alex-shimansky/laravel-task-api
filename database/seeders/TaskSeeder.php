<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $pm = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('123'),
            'name' => 'Project manager',
        ]);

        $teamLead = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('123'),
            'name' => 'Team Lead',
        ]);

        $developer = User::factory()->create([
            'email' => 'test3@example.com',
            'password' => bcrypt('123'),
            'name' => 'Developer',
        ]);

        $task1 = Task::create([
            'user_id' => $pm->id,
            'assignee_id' => $developer->id,
            'title' => 'Project Planning',
            'description' => 'Plan the overall project',
            'priority' => 2,
            'status' => 'todo',
        ]);

        $subtask1 = Task::create([
            'user_id' => $pm->id,
            'assignee_id' => $developer->id,
            'parent_id' => $task1->id,
            'title' => 'Gather requirements',
            'description' => 'Collect functional and non-functional requirements from stakeholders to define the necessary features and constraints.',
            'priority' => 3,
            'status' => 'done',
            'completed_at' => Carbon::now(),
        ]);

        $subtask2 = Task::create([
            'user_id' => $pm->id,
            'assignee_id' => $developer->id,
            'parent_id' => $task1->id,
            'title' => 'Define milestones',
            'priority' => 4,
            'status' => 'todo',
        ]);

        Task::create([
            'user_id' => $pm->id,
            'assignee_id' => $developer->id,
            'title' => 'Setup project repository',
            'priority' => 1,
            'status' => 'done',
            'completed_at' => Carbon::now()->subDay(),
        ]);

        Task::create([
            'user_id' => $teamLead->id,
            'assignee_id' => $developer->id,
            'title' => 'Initialize CI/CD pipeline',
            'description' => 'Set up continuous integration and delivery for automated testing and deployment.',
            'priority' => 1,
            'status' => 'done',
            'completed_at' => Carbon::now()->subDay(),
        ]);
    }
}
