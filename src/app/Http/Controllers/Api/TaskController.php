<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function __construct(
        protected TaskRepository $taskRepository
    ){}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $filters = [];

        if ($request->has('status')) {
            $filters['status'] = TaskStatus::from($request->input('status'))->value;
        }

        if ($request->has('priority')) {
            $priority = (int) $request->input('priority');
            if ($priority >= 1 && $priority <= 5) {
                $filters['priority'] = $priority;
            }
        }

        if ($request->has('title')) {
            $filters['title'] = $request->input('title');
        }

        if ($request->has('description')) {
            $filters['description'] = $request->input('description');
        }

        $sorts = [];

        if ($request->has('sort_by')) {
            $allowedSortFields = ['created_at', 'completed_at', 'priority'];
            $allowedSortDirections = ['asc', 'desc'];

            $sortCriteria = explode(',', $request->input('sort_by'));

            foreach ($sortCriteria as $sortCriterion) {
                $parts = explode(':', $sortCriterion);
                $field = $parts[0];
                $direction = count($parts) > 1 ? strtolower($parts[1]) : 'asc';

                if (in_array($field, $allowedSortFields) && in_array($direction, $allowedSortDirections)) {
                    $sorts[] = ['field' => $field, 'direction' => $direction];
                }
            }
        } else {
            $sorts[] = ['field' => 'id', 'direction' => 'desc'];
        }

        $tasks = $this->taskRepository->getTasksForUser($userId, $filters, $sorts);

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['integer', 'min:1', 'max:5'],
        ]);

        $data = [
            'user_id' => $request->user()->id,
            'parent_id' => $request->input('parent_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'todo'),
            'priority' => $request->input('priority', 5),
        ];

        $task = $this->taskRepository->createTask($data);

        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['integer', 'min:1', 'max:5'],
        ]);

        if ($task->status === TaskStatus::Done) {
            return response()->json(['message' => 'You can\'t change a task that has already been completed'], 400);
        }

        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => $request->input('priority'),
        ];

        $updatedTask = $this->taskRepository->updateTask($task, $data);

        return response()->json($updatedTask);
    }

    public function complete(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        if ($task->status === TaskStatus::Done) {
            return response()->json(['message' => 'Task already done!'], 400);
        }

        if (!$this->taskRepository->areAllChildrenDone($task->id)) {
            return response()->json(['message' => 'Cannot complete task: Some child tasks are not done!'], 400);
        }

        $updatedTask = $this->taskRepository->markTaskAsDone($task);

        return response()->json($updatedTask);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if ($task->status === TaskStatus::Done) {
            return response()->json(['message' => 'You cannot delete a completed task.'], 400);
        }

        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You cannot delete a task.'], 400);
        }

        $this->taskRepository->deleteTask($task);

        return response()->json(['message' => 'The task was successfully deleted.']);
    }
}
