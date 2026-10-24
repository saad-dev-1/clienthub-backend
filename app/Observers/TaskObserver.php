<?php
namespace App\Observers;

use App\Models\Task;
use App\Models\Activity;

class TaskObserver
{
    public function created(Task $task)
    {
        Activity::create([
            'user_id' => $task->project->user_id,
            'type' => 'task',
            'action' => 'created',
            'description' => "Task \"{$task->title}\" was added to {$task->project->name}",
        ]);
    }

    public function updated(Task $task)
    {
        if ($task->wasChanged('status') && $task->status === 'done') {
            Activity::create([
                'user_id' => $task->project->user_id,
                'type' => 'task',
                'action' => 'completed',
                'description' => "Task \"{$task->title}\" was completed",
            ]);
        }
    }
}