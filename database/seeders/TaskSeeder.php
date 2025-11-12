<?php 
namespace Database\Seeders;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $teamMembers = User::where('role', 'team_member')->get();
        $admin = User::where('role', 'admin')->first();

        $taskTemplates = [
            ['title' => 'Setup project repository', 'priority' => 'high', 'status' => 'completed'],
            ['title' => 'Design database schema', 'priority' => 'high', 'status' => 'completed'],
            ['title' => 'Create wireframes', 'priority' => 'medium', 'status' => 'completed'],
            ['title' => 'Implement authentication system', 'priority' => 'high', 'status' => 'in_progress'],
            ['title' => 'Build REST API endpoints', 'priority' => 'high', 'status' => 'in_progress'],
            ['title' => 'Design UI components', 'priority' => 'medium', 'status' => 'review'],
            ['title' => 'Implement payment gateway', 'priority' => 'urgent', 'status' => 'todo'],
            ['title' => 'Setup CI/CD pipeline', 'priority' => 'medium', 'status' => 'todo'],
            ['title' => 'Write unit tests', 'priority' => 'medium', 'status' => 'todo'],
            ['title' => 'Perform security audit', 'priority' => 'high', 'status' => 'todo'],
            ['title' => 'Deploy to staging server', 'priority' => 'low', 'status' => 'todo'],
            ['title' => 'User acceptance testing', 'priority' => 'high', 'status' => 'todo'],
        ];

        foreach ($projects as $project) {
            // Create 8-12 tasks per project
            $taskCount = rand(8, 12);
            
            for ($i = 0; $i < $taskCount; $i++) {
                $template = $taskTemplates[array_rand($taskTemplates)];
                
                $startDate = now()->subDays(rand(1, 60));
                $dueDate = $startDate->copy()->addDays(rand(7, 30));
                
                Task::create([
                    'project_id' => $project->id,
                    'title' => $template['title'] . ' - ' . $project->name,
                    'description' => 'This task involves ' . strtolower($template['title']) . ' for the project. It requires careful planning and execution.',
                    'assigned_to' => $teamMembers->random()->id,
                    'created_by' => $admin->id,
                    'priority' => $template['priority'],
                    'status' => $template['status'],
                    'start_date' => $startDate,
                    'due_date' => $dueDate,
                    'completed_at' => $template['status'] === 'completed' ? $dueDate : null,
                    'estimated_hours' => rand(8, 80),
                    'actual_hours' => $template['status'] === 'completed' ? rand(8, 80) : rand(0, 40),
                    'progress' => $template['status'] === 'completed' ? 100 : rand(0, 90),
                ]);
            }
        }
    }
}