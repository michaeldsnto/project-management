<?php 
namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $teamMembers = User::where('role', 'team_member')->get();

        $roles = ['lead', 'developer', 'designer', 'tester', 'analyst'];

        foreach ($projects as $project) {
            // Add 4-6 team members per project
            $memberCount = rand(4, 6);
            $selectedMembers = $teamMembers->random($memberCount);
            
            foreach ($selectedMembers as $index => $member) {
                TeamMember::create([
                    'user_id' => $member->id,
                    'project_id' => $project->id,
                    'role' => $index === 0 ? 'lead' : $roles[array_rand($roles)],
                    'allocation_percentage' => rand(50, 100),
                    'joined_at' => $project->start_date,
                    'is_active' => true,
                ]);
            }
        }
    }
}