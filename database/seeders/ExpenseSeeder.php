<?php 
namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $teamMembers = User::where('role', 'team_member')->get();
        $managers = User::where('role', 'project_manager')->get();

        $expenseTemplates = [
            ['title' => 'Software License Purchase', 'category' => 'software', 'amount_range' => [5000000, 20000000]],
            ['title' => 'Cloud Server Hosting', 'category' => 'software', 'amount_range' => [2000000, 10000000]],
            ['title' => 'Development Equipment', 'category' => 'equipment', 'amount_range' => [10000000, 30000000]],
            ['title' => 'Team Salary - Monthly', 'category' => 'salary', 'amount_range' => [50000000, 100000000]],
            ['title' => 'Client Meeting Travel', 'category' => 'travel', 'amount_range' => [3000000, 8000000]],
            ['title' => 'Training and Certification', 'category' => 'miscellaneous', 'amount_range' => [5000000, 15000000]],
            ['title' => 'Office Supplies', 'category' => 'miscellaneous', 'amount_range' => [1000000, 5000000]],
        ];

        foreach ($projects as $project) {
            // Create 5-10 expenses per project
            $expenseCount = rand(5, 10);
            
            for ($i = 0; $i < $expenseCount; $i++) {
                $template = $expenseTemplates[array_rand($expenseTemplates)];
                $amount = rand($template['amount_range'][0], $template['amount_range'][1]);
                
                $status = ['pending', 'approved', 'approved', 'approved', 'rejected'][array_rand(['pending', 'approved', 'approved', 'approved', 'rejected'])];
                $expenseDate = now()->subDays(rand(1, 90));
                
                Expense::create([
                    'project_id' => $project->id,
                    'user_id' => $teamMembers->random()->id,
                    'title' => $template['title'],
                    'description' => 'Expense for ' . strtolower($template['title']) . ' as part of project requirements.',
                    'amount' => $amount,
                    'category' => $template['category'],
                    'expense_date' => $expenseDate,
                    'status' => $status,
                    'approved_by' => $status !== 'pending' ? $managers->random()->id : null,
                    'approved_at' => $status !== 'pending' ? $expenseDate->copy()->addDays(rand(1, 5)) : null,
                ]);
            }
        }
    }
}