<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'name',
        'description',
        'status',
        'progress',
        'deadline',
        'share_token',
        'is_public',
        'client_feedback',
        'client_approved',
        'client_feedback_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'progress' => 'integer',
        'is_public' => 'boolean',
        'client_approved' => 'boolean',
        'client_feedback_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}