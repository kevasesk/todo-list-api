<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::where('user_id', $request->user()->id);

        if ($request->has('status') && in_array($request->status, ['todo', 'done'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $priority = (int) $request->priority;

            if ($priority >= 1 && $priority <= 5) {
                $query->where('priority', $priority);
            }
        }

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        if ($request->has('sort_by')) {
            $allowedSortFields = ['created_at', 'completed_at', 'priority'];
            $allowedSortDirections = ['asc', 'desc'];

            $sorts = explode(',', $request->sort_by);

            foreach ($sorts as $sortCriterion) {
                $parts = explode(':', $sortCriterion);
                $field = $parts[0];
                $direction = count($parts) > 1 ? strtolower($parts[1]) : 'asc';

                if (in_array($field, $allowedSortFields) && in_array($direction, $allowedSortDirections)) {
                    $query->orderBy($field, $direction);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $allUserTasks = $query->get();

        return response()->json($allUserTasks);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => Rule::in(['todo', 'done']),
            'priority' => 'integer|min:1|max:5',
        ]);

        $task = new Task();
        $task->user_id = $request->user()->id;
        $task->parent_id = $request->input('parent_id');
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->status = $request->input('status', 'todo');
        $task->priority = $request->input('priority', 5);
        $task->completed_at = $task->status === 'done' ? Carbon::now() : null;

        $task->save();

        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => Rule::in(['todo', 'done']),
            'priority' => 'nullable|integer|min:1|max:5',
        ]);

        if ($task->status === 'done') {
            return response()->json(['message' => 'You can\'t change a task that has already been completed'], 400);
        }

        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->priority = $request->input('priority');

        $task->save();

        return response()->json($task);
    }

    public function complete(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        if ($task->status === 'done') {
            return response()->json(['message' => 'Task already done!'], 400);
        }

        if (!$this->areAllChildrenDone($task)) {
            return response()->json(['message' => 'Cannot complete task: Some child tasks are not done!'], 400);
        }

        $task->status = 'done';
        $task->completed_at = Carbon::now();

        $task->save();

        return response()->json($task);
    }

    private function areAllChildrenDone(Task $task): bool
    {
        // Get all direct children
        $children = Task::where('parent_id', $task->id)->get();

        // If no children, return true
        if ($children->isEmpty()) {
            return true;
        }

        // Check each child
        foreach ($children as $child) {
            // If child is not done, return false
            if ($child->status !== 'done') {
                return false;
            }

            // Recursively check child's children
            if (!$this->areAllChildrenDone($child)) {
                return false;
            }
        }

        return true;
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->status === 'done') {
            return response()->json(['message' => 'You cannot delete a completed task.'], 400);
        }

        $task->delete();

        return response()->json(['message' => 'The task was successfully deleted.']);
    }
}
