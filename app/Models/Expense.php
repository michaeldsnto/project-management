<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'description',
        'amount',
        'category',
        'expense_date',
        'receipt',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // User who created
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // User who approved
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Check if approved
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    // Approve expense
    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }
}
