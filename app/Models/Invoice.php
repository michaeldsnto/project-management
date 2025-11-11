<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'project_id',
        'client_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Client
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Check if overdue
    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    // Mark as paid
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}