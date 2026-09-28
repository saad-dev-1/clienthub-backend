<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function showProject(string $token)
    {
        $project = Project::where('share_token', $token)
            ->where('is_public', true)
            ->with(['client:id,name,company', 'tasks', 'user:id,name', 'attachments'])
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
            'files' => $project->attachments->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'size' => $file->size,
                    'mime_type' => $file->mime_type,
                    'created_at' => $file->created_at,
                    'url' => url("/api/attachments/{$file->id}/download"),
                ];
            }),
            'owner' => [
                'name' => $project->user->name,
            ],
            'feedback' => [
                'approved' => $project->client_approved,
                'comment' => $project->client_feedback,
                'submitted_at' => $project->client_feedback_at,
            ],
        ]);
    }

    public function submitFeedback(Request $request, string $token)
    {
        $project = Project::where('share_token', $token)
            ->where('is_public', true)
            ->firstOrFail();

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $project->update([
            'client_approved' => $validated['approved'],
            'client_feedback' => $validated['feedback'] ?? null,
            'client_feedback_at' => now(),
        ]);

        return response()->json([
            'message' => 'Thank you for your feedback!',
            'feedback' => [
                'approved' => $project->client_approved,
                'comment' => $project->client_feedback,
                'submitted_at' => $project->client_feedback_at,
            ],
        ]);
    }
}