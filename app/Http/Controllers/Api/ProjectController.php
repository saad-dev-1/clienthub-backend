<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()
            ->projects()
            ->with('client:id,name')
            ->latest()
            ->get();

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'description' => 'nullable|string',
            'status' => 'in:active,completed,on_hold',
            'progress' => 'integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        $project = $request->user()->projects()->create($validated);

        return response()->json($project->load('client:id,name'), 201);
    }

    public function show(Request $request, Project $project)
    {
        $this->authorizeOwnership($request, $project);

        return response()->json($project->load('client:id,name'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeOwnership($request, $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'nullable|exists:clients,id',
            'description' => 'nullable|string',
            'status' => 'in:active,completed,on_hold',
            'progress' => 'integer|min:0|max:100',
            'deadline' => 'nullable|date',
        ]);

        $project->update($validated);

        return response()->json($project->load('client:id,name'));
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorizeOwnership($request, $project);
        $project->delete();

        return response()->json(['message' => 'Project deleted.']);
    }

    protected function authorizeOwnership(Request $request, Project $project): void
    {
        if ($project->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}