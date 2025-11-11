<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'hours',
        'log_date',
        'description',
    ];

    protected $casts = [
        'log_date' => 'date',
        'hours' => 'decimal:2',
    ];

    // Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
