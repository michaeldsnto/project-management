<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'progress',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'date',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'progress' => 'integer',
    ];

    // Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Assigned user
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Attachments
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // Time logs
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    // Calculate total logged hours
    public function getTotalLoggedHoursAttribute()
    {
        return $this->timeLogs()->sum('hours');
    }

    // Check if task is overdue
    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() && $this->status !== 'completed';
    }

    // Check if task is completed
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // Mark as completed
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);
    }
}
