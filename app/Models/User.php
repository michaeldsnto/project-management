<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'bio',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Projects managed by this user
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    // Projects where this user is a client
    public function clientProjects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    // Projects where this user is a team member
    public function teamProjects()
    {
        return $this->belongsToMany(Project::class, 'team_members')
            ->withPivot('role', 'allocation_percentage', 'joined_at', 'is_active')
            ->withTimestamps();
    }

    // Tasks assigned to this user
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // Tasks created by this user
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // Comments made by this user
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Time logs by this user
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    // Expenses created by this user
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is project manager
    public function isProjectManager()
    {
        return $this->role === 'project_manager';
    }

    // Check if user is client
    public function isClient()
    {
        return $this->role === 'client';
    }
}
