<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
        'parent_id',
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

    // Parent comment (for replies)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
