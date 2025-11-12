<?php 
namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $managers = User::where('role', 'project_manager')->get();
        $clients = User::where('role', 'client')->get();

        $projects = [
            [
                'name' => 'E-Commerce Platform Development',
                'code' => 'PRJ-001',
                'description' => 'Build a modern e-commerce platform with shopping cart, payment gateway, and inventory management system.',
                'manager_id' => $managers[0]->id,
                'client_id' => $clients[0]->id,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(3),
                'budget' => 500000000,
                'actual_cost' => 250000000,
                'status' => 'in_progress',
                'progress' => 60,
                'priority' => 'high',
            ],
            [
                'name' => 'Mobile App for Food Delivery',
                'code' => 'PRJ-002',
                'description' => 'Create iOS and Android app for food delivery service with real-time tracking and payment integration.',
                'manager_id' => $managers[1]->id,
                'client_id' => $clients[1]->id,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
                'budget' => 350000000,
                'actual_cost' => 100000000,
                'status' => 'in_progress',
                'progress' => 40,
                'priority' => 'urgent',
            ],
            [
                'name' => 'Company Website Redesign',
                'code' => 'PRJ-003',
                'description' => 'Redesign corporate website with modern UI/UX and SEO optimization.',
                'manager_id' => $managers[0]->id,
                'client_id' => $clients[0]->id,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(2),
                'budget' => 150000000,
                'actual_cost' => 75000000,
                'status' => 'in_progress',
                'progress' => 75,
                'priority' => 'medium',
            ],
            [
                'name' => 'Customer Relationship Management System',
                'code' => 'PRJ-004',
                'description' => 'Develop comprehensive CRM system for managing customer data, sales pipeline, and reporting.',
                'manager_id' => $managers[1]->id,
                'client_id' => $clients[1]->id,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addMonths(6),
                'budget' => 600000000,
                'actual_cost' => 50000000,
                'status' => 'planning',
                'progress' => 15,
                'priority' => 'high',
            ],
            [
                'name' => 'Inventory Management Dashboard',
                'code' => 'PRJ-005',
                'description' => 'Build real-time inventory tracking dashboard with analytics and forecasting.',
                'manager_id' => $managers[0]->id,
                'client_id' => $clients[0]->id,
                'start_date' => now()->subMonths(4),
                'end_date' => now()->subMonths(1),
                'budget' => 200000000,
                'actual_cost' => 195000000,
                'status' => 'completed',
                'progress' => 100,
                'priority' => 'medium',
            ],
        ];

        foreach ($projects as $projectData) {
            Project::create($projectData);
        }
    }
}