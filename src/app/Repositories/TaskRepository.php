<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Support\Collection;

class TaskRepository
{
    public function getTasksForUser(int $userId, array $filters = [], array $sorts = []): Collection
    {
        $query = Task::where('user_id', $userId);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }
        if (isset($filters['description'])) {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
        }

        // Apply sorting
        if (!empty($sorts)) {
            foreach ($sorts as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->get();
    }

    public function createTask(array $data): Task
    {
        $task = new Task();
        $task->user_id = $data['user_id'];
        $task->parent_id = $data['parent_id'] ?? null;
        $task->title = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->status = $data['status'] ?? 'todo';
        $task->priority = $data['priority'] ?? 5;
        $task->completed_at = $task->status === TaskStatus::Done->value ? now() : null;
        $task->save();

        return $task;
    }

    public function getTaskById(int $id): ?Task
    {
        return Task::find($id);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->title = $data['title'];
        $task->description = $data['description'] ?? $task->description;
        $task->priority = $data['priority'] ?? $task->priority;
        $task->save();

        return $task;
    }

    public function markTaskAsDone(Task $task): Task
    {
        $task->status = TaskStatus::Done->value;
        $task->completed_at = now();
        $task->save();

        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    public function areAllChildrenDone(int $taskId): bool
    {
        $task = Task::find($taskId);
        if (!$task) {
            return true; // If task doesn't exist, consider it as having no children
        }

        $children = Task::where('parent_id', $taskId)->get();

        foreach ($children as $child) {
            if ($child->status !== TaskStatus::Done->value) {
                return false;
            }
            if (!$this->areAllChildrenDone($child->id)) {
                return false;
            }
        }

        return true;
    }
}
