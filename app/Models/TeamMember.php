<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'role',
        'allocation_percentage',
        'joined_at',
        'left_at',
        'is_active',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'left_at' => 'date',
        'is_active' => 'boolean',
        'allocation_percentage' => 'integer',
    ];

    // User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}