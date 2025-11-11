<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'client_id',
        'manager_id',
        'start_date',
        'end_date',
        'budget',
        'actual_cost',
        'status',
        'progress',
        'priority',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress' => 'integer',
    ];

    // Project manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Client
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Team members
    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role', 'allocation_percentage', 'joined_at', 'is_active')
            ->withTimestamps();
    }

    // Active team members only
    public function activeTeamMembers()
    {
        return $this->teamMembers()->wherePivot('is_active', true);
    }

    // Tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Expenses
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Invoices
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Calculate total expenses
    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->where('status', 'approved')->sum('amount');
    }

    // Calculate budget remaining
    public function getBudgetRemainingAttribute()
    {
        return $this->budget - $this->actual_cost;
    }

    // Check if project is overbudget
    public function isOverBudget()
    {
        return $this->actual_cost > $this->budget;
    }

    // Calculate completion percentage
    public function calculateProgress()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;

        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return round(($completedTasks / $totalTasks) * 100);
    }
}