<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    // Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Uploader
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Get file size in human readable format
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
