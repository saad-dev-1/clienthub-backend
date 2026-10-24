<?php
namespace App\Observers;

use App\Models\Project;
use App\Models\Activity;

class ProjectObserver
{
    public function created(Project $project)
    {
        Activity::create([
            'user_id' => $project->user_id,
            'type' => 'project',
            'action' => 'created',
            'description' => "Project \"{$project->name}\" was created",
        ]);
    }
}