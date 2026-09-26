<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;

class PublicController extends Controller
{
    public function showProject(string $token)
    {
        $project = Project::where('share_token', $token)
            ->where('is_public', true)
            ->with(['client:id,name,company', 'tasks', 'user:id,name'])
            ->firstOrFail();

        return response()->json([
            'project' => [
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'progress' => $project->progress,
                'deadline' => $project->deadline,
                'client' => $project->client,
            ],
            'tasks' => $project->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'due_date' => $task->due_date,
                ];
            }),
            'owner' => [
                'name' => $project->user->name,
            ],
        ]);
    }
}