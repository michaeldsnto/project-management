<?php 
namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = Task::all();
        $users = User::whereIn('role', ['admin', 'project_manager', 'team_member'])->get();

        $commentTemplates = [
            'Great progress on this task!',
            'Please update the status when completed.',
            'I have reviewed the work and it looks good.',
            'Can we schedule a meeting to discuss this?',
            'This needs some revisions before we can proceed.',
            'Excellent work! Keep it up.',
            'I have some questions about the implementation.',
            'The deadline might need to be extended.',
            'All tests are passing now.',
            'Please add documentation for this feature.',
            'I\'ve merged the changes to the main branch.',
            'Let\'s discuss the best approach for this.',
        ];

        foreach ($tasks as $task) {
            // Add 2-5 comments per task
            $commentCount = rand(2, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'task_id' => $task->id,
                    'user_id' => $users->random()->id,
                    'content' => $commentTemplates[array_rand($commentTemplates)],
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}