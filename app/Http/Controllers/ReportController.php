<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get available projects for reports
        if ($user->isAdmin() || $user->isProjectManager()) {
            $projects = Project::with('manager', 'client')->get();
        } else {
            $projects = $user->teamProjects()->with('manager')->get();
        }

        return view('reports.index', compact('projects'));
    }

    public function project(Project $project)
    {
        // Check access
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isProjectManager() && !$project->teamMembers()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        // Project statistics
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $project->tasks()->where('status', 'todo')->count(),
            'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            'total_expenses' => $project->expenses()->where('status', 'approved')->sum('amount'),
            'total_hours' => $project->tasks()->sum('actual_hours'),
            'budget_used' => $project->actual_cost,
            'budget_remaining' => $project->budget_remaining,
            'progress' => $project->calculateProgress(),
        ];

        // Tasks breakdown
        $tasks = $project->tasks()
            ->with('assignedUser', 'timeLogs')
            ->orderBy('created_at', 'desc')
            ->get();

        // Expenses breakdown
        $expenses = $project->expenses()
            ->with('user')
            ->where('status', 'approved')
            ->orderBy('expense_date', 'desc')
            ->get();

        // Team members and their contributions
        $teamMembers = $project->activeTeamMembers()
            ->with(['tasks' => function($query) use ($project) {
                $query->where('project_id', $project->id);
            }])
            ->get()
            ->map(function($member) use ($project) {
                $member->total_tasks = $member->tasks->count();
                $member->completed_tasks = $member->tasks->where('status', 'completed')->count();
                $member->total_hours = $member->tasks->sum('actual_hours');
                return $member;
            });

        return view('reports.project', compact('project', 'stats', 'tasks', 'expenses', 'teamMembers'));
    }

    public function financial()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isProjectManager()) {
            // Overall financial summary
            $stats = [
                'total_projects' => Project::count(),
                'active_projects' => Project::where('status', 'in_progress')->count(),
                'total_budget' => Project::sum('budget'),
                'total_actual_cost' => Project::sum('actual_cost'),
                'total_expenses' => Expense::where('status', 'approved')->sum('amount'),
                'total_invoiced' => Invoice::where('status', 'paid')->sum('total'),
                'pending_invoices' => Invoice::where('status', 'sent')->sum('total'),
                'overdue_invoices' => Invoice::where('status', 'overdue')->sum('total'),
            ];

            // Projects financial breakdown
            $projects = Project::with('expenses', 'invoices')
                ->get()
                ->map(function($project) {
                    $project->total_expenses = $project->expenses()->where('status', 'approved')->sum('amount');
                    $project->total_invoiced = $project->invoices()->where('status', 'paid')->sum('total');
                    $project->profit_loss = $project->total_invoiced - $project->actual_cost;
                    return $project;
                });

            // Monthly expenses for the last 12 months
            $monthlyExpenses = Expense::where('status', 'approved')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

        } else {
            // Team member view - only their projects
            $projects = $user->teamProjects()->with('expenses', 'invoices')->get();

            $stats = [
                'my_projects' => $projects->count(),
                'total_budget' => $projects->sum('budget'),
                'total_actual_cost' => $projects->sum('actual_cost'),
                'total_expenses' => Expense::where('status', 'approved')
                    ->whereIn('project_id', $projects->pluck('id'))
                    ->sum('amount'),
            ];
        }

        return view('reports.financial', compact('stats', 'projects', 'monthlyExpenses'));
    }

    public function team()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isProjectManager()) {
            // All team members
            $teamMembers = User::where('is_active', true)
                ->whereIn('role', ['project_manager', 'team_member'])
                ->with(['tasks', 'timeLogs', 'teamProjects'])
                ->get()
                ->map(function($member) {
                    $member->total_tasks = $member->tasks()->count();
                    $member->completed_tasks = $member->tasks()->where('status', 'completed')->count();
                    $member->total_hours = $member->timeLogs()->sum('hours');
                    $member->active_projects = $member->teamProjects()->where('status', 'in_progress')->count();
                    $member->completion_rate = $member->total_tasks > 0 ? round(($member->completed_tasks / $member->total_tasks) * 100, 1) : 0;
                    return $member;
                })
                ->sortByDesc('total_hours');
        } else {
            // Only current user
            $teamMembers = collect([$user])->map(function($member) {
                $member->total_tasks = $member->tasks()->count();
                $member->completed_tasks = $member->tasks()->where('status', 'completed')->count();
                $member->total_hours = $member->timeLogs()->sum('hours');
                $member->active_projects = $member->teamProjects()->where('status', 'in_progress')->count();
                $member->completion_rate = $member->total_tasks > 0 ? round(($member->completed_tasks / $member->total_tasks) * 100, 1) : 0;
                return $member;
            });
        }

        // Overall team statistics
        $stats = [
            'total_members' => $teamMembers->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'total_hours' => $teamMembers->sum('total_hours'),
            'average_completion_rate' => $teamMembers->avg('completion_rate'),
        ];

        return view('reports.team', compact('teamMembers', 'stats'));
    }

    public function exportProject(Project $project)
    {
        // Check access
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isProjectManager() && !$project->teamMembers()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        // For now, return JSON - in production, implement PDF/Excel export
        $data = [
            'project' => $project->toArray(),
            'tasks' => $project->tasks()->with('assignedUser')->get(),
            'expenses' => $project->expenses()->where('status', 'approved')->get(),
            'team_members' => $project->activeTeamMembers,
        ];

        return response()->json($data);
    }

    public function exportFinancial()
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isProjectManager()) {
            $data = [
                'projects' => Project::with('expenses', 'invoices')->get(),
                'total_budget' => Project::sum('budget'),
                'total_actual_cost' => Project::sum('actual_cost'),
                'total_expenses' => Expense::where('status', 'approved')->sum('amount'),
                'total_invoiced' => Invoice::where('status', 'paid')->sum('total'),
            ];
        } else {
            $projects = $user->teamProjects()->with('expenses')->get();
            $data = [
                'projects' => $projects,
                'total_budget' => $projects->sum('budget'),
                'total_actual_cost' => $projects->sum('actual_cost'),
                'total_expenses' => Expense::where('status', 'approved')
                    ->whereIn('project_id', $projects->pluck('id'))
                    ->sum('amount'),
            ];
        }

        return response()->json($data);
    }
}
