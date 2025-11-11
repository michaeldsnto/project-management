<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistics based on user role
        if ($user->isAdmin() || $user->isProjectManager()) {
            $stats = [
                'total_projects' => Project::count(),
                'active_projects' => Project::where('status', 'in_progress')->count(),
                'total_tasks' => Task::count(),
                'pending_tasks' => Task::where('status', 'todo')->count(),
                'total_expenses' => Expense::where('status', 'approved')->sum('amount'),
            ];
            
            $recentProjects = Project::with('manager', 'client')
                ->latest()
                ->take(5)
                ->get();
                
            $upcomingDeadlines = Task::with('project', 'assignedUser')
                ->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->orderBy('due_date')
                ->take(10)
                ->get();
        } else {
            // Team member view
            $stats = [
                'my_projects' => $user->teamProjects()->count(),
                'my_tasks' => $user->tasks()->count(),
                'pending_tasks' => $user->tasks()->where('status', 'todo')->count(),
                'completed_tasks' => $user->tasks()->where('status', 'completed')->count(),
            ];
            
            $recentProjects = $user->teamProjects()
                ->with('manager')
                ->latest()
                ->take(5)
                ->get();
                
            $upcomingDeadlines = $user->tasks()
                ->with('project')
                ->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->orderBy('due_date')
                ->take(10)
                ->get();
        }
        
        return view('dashboard.index', compact('stats', 'recentProjects', 'upcomingDeadlines'));
    }
}
