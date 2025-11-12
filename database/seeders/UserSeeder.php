<?php 
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'bio' => 'System Administrator',
            'is_active' => true,
        ]);

        // Project Managers
        User::create([
            'name' => 'John Manager',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
            'phone' => '081234567891',
            'bio' => 'Senior Project Manager with 10 years experience',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Sarah Williams',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
            'phone' => '081234567892',
            'bio' => 'Project Manager specializing in web development',
            'is_active' => true,
        ]);

        // Team Members
        $teamMembers = [
            ['name' => 'Alice Developer', 'email' => 'alice@example.com'],
            ['name' => 'Bob Designer', 'email' => 'bob@example.com'],
            ['name' => 'Charlie Tester', 'email' => 'charlie@example.com'],
            ['name' => 'Diana Developer', 'email' => 'diana@example.com'],
            ['name' => 'Edward Frontend', 'email' => 'edward@example.com'],
            ['name' => 'Fiona Backend', 'email' => 'fiona@example.com'],
            ['name' => 'George Analyst', 'email' => 'george@example.com'],
            ['name' => 'Hannah DevOps', 'email' => 'hannah@example.com'],
        ];

        foreach ($teamMembers as $index => $member) {
            User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make('password'),
                'role' => 'team_member',
                'phone' => '08123456789' . (3 + $index),
                'bio' => 'Experienced team member',
                'is_active' => true,
            ]);
        }

        // Clients
        User::create([
            'name' => 'PT. Teknologi Indonesia',
            'email' => 'client1@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '081234567899',
            'bio' => 'Technology company',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'CV. Digital Solutions',
            'email' => 'client2@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '081234567898',
            'bio' => 'Digital marketing agency',
            'is_active' => true,
        ]);
    }
}