<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeProjectsCount = $user->projects()
            ->where('status', 'active')
            ->count();

        $clientsCount = $user->clients()->count();

        // Pending tasks = tasks jinka status 'done' nahi hai, user ke projects mein
        $pendingTasksCount = \App\Models\Task::whereHas('project', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', '!=', 'done')->count();

        $recentProjects = $user->projects()
            ->with('client:id,name')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'progress' => $project->progress,
                    'deadline' => $project->deadline,
                    'client' => $project->client,
                ];
            });

        return response()->json([
            'stats' => [
                'active_projects' => $activeProjectsCount,
                'clients' => $clientsCount,
                'pending_tasks' => $pendingTasksCount,
                'revenue' => 0, // Invoices banne ke baad update karenge
            ],
            'recent_projects' => $recentProjects,
        ]);
    }
}