<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function indexAll(Request $request)
    {
        $tasks = Task::whereHas('project', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->with('project:id,name')
            ->latest()
            ->get();

        return response()->json($tasks);
    }

    public function index(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        return response()->json(
            $project->tasks()->orderBy('order')->orderBy('created_at')->get()
        );
    }

    public function store(Request $request, Project $project)
    {
        $this->authorizeProject($request, $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:todo,doing,done',
            'due_date' => 'nullable|date',
        ]);

        $task = $project->tasks()->create($validated);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:todo,doing,done',
            'due_date' => 'nullable|date',
            'order' => 'integer',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    protected function authorizeProject(Request $request, Project $project): void
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }

    protected function authorizeTask(Request $request, Task $task): void
    {
        if ($task->project->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}