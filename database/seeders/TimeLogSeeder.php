<?php 
namespace Database\Seeders;

use App\Models\TimeLog;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TimeLogSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = Task::whereIn('status', ['in_progress', 'review', 'completed'])->get();

        foreach ($tasks as $task) {
            if (!$task->assigned_to) continue;

            // Add 1-5 time logs per task
            $logCount = rand(1, 5);
            
            for ($i = 0; $i < $logCount; $i++) {
                $logDate = now()->subDays(rand(1, 30));
                
                TimeLog::create([
                    'task_id' => $task->id,
                    'user_id' => $task->assigned_to,
                    'hours' => rand(1, 8) + (rand(0, 1) * 0.5), // 1.0, 1.5, 2.0, etc.
                    'log_date' => $logDate,
                    'description' => 'Working on ' . strtolower($task->title),
                ]);
            }
        }
    }
}