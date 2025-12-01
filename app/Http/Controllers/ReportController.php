<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports index page
     */
    public function index()
    {
        $projects = Project::with('manager')->get();
        
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_budget' => Project::sum('budget'),
            'total_spent' => Project::sum('actual_cost'),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
        ];

        return view('reports.index', compact('projects', 'stats'));
    }

    /**
     * Generate project report
     */
    public function project(Project $project)
    {
        $project->load([
            'manager',
            'client',
            'tasks.assignedUser',
            'teamMembers',
            'expenses' => function ($query) {
                $query->where('status', 'approved');
            }
        ]);

        // Calculate statistics
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $project->tasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $project->tasks->where('status', 'todo')->count(),
            'overdue_tasks' => $project->tasks->filter(fn($task) => $task->isOverdue())->count(),
            'total_expenses' => $project->expenses->sum('amount'),
            'budget_utilization' => $project->budget > 0 ? ($project->actual_cost / $project->budget) * 100 : 0,
            'team_size' => $project->teamMembers->count(),
        ];

        // Task breakdown by priority
        $tasksByPriority = $project->tasks->groupBy('priority')->map(fn($group) => $group->count());

        // Task breakdown by status
        $tasksByStatus = $project->tasks->groupBy('status')->map(fn($group) => $group->count());

        // Expense breakdown by category
        $expensesByCategory = $project->expenses->groupBy('category')->map(fn($group) => $group->sum('amount'));

        return view('reports.project', compact('project', 'stats', 'tasksByPriority', 'tasksByStatus', 'expensesByCategory'));
    }

    /**
     * Export project report to PDF
     */
    public function exportProject(Project $project)
    {
        $project->load([
            'manager',
            'client',
            'tasks.assignedUser',
            'teamMembers',
            'expenses' => function ($query) {
                $query->where('status', 'approved');
            }
        ]);

        // Calculate statistics
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $project->tasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $project->tasks->where('status', 'todo')->count(),
            'overdue_tasks' => $project->tasks->filter(fn($task) => $task->isOverdue())->count(),
            'total_expenses' => $project->expenses->sum('amount'),
            'budget_utilization' => $project->budget > 0 ? ($project->actual_cost / $project->budget) * 100 : 0,
            'team_size' => $project->teamMembers->count(),
        ];

        $tasksByPriority = $project->tasks->groupBy('priority')->map(fn($group) => $group->count());
        $tasksByStatus = $project->tasks->groupBy('status')->map(fn($group) => $group->count());
        $expensesByCategory = $project->expenses->groupBy('category')->map(fn($group) => $group->sum('amount'));

        $pdf = Pdf::loadView('reports.pdf.project', compact('project', 'stats', 'tasksByPriority', 'tasksByStatus', 'expensesByCategory'));
        
        return $pdf->download('project-report-' . $project->code . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Display financial report
     */
    public function financial(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        // Total budget and costs
        $projects = Project::whereBetween('start_date', [$startDate, $endDate])->get();
        
        $totalBudget = $projects->sum('budget');
        $totalSpent = $projects->sum('actual_cost');
        $totalRemaining = $totalBudget - $totalSpent;

        // Expenses breakdown
        $expensesByCategory = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        // Expenses by project
        $expensesByProject = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('project')
            ->get()
            ->groupBy('project_id')
            ->map(function ($expenses) {
                return [
                    'project' => $expenses->first()->project,
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            });

        // Monthly trend (last 6 months)
        $monthlyExpenses = Expense::where('status', 'approved')
            ->where('expense_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $stats = [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'utilization_rate' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
            'total_projects' => $projects->count(),
            'overbudget_projects' => $projects->filter(fn($p) => $p->isOverBudget())->count(),
        ];

        return view('reports.financial', compact('stats', 'expensesByCategory', 'expensesByProject', 'monthlyExpenses', 'startDate', 'endDate'));
    }

    /**
     * Export financial report to PDF
     */
    public function exportFinancial(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $projects = Project::whereBetween('start_date', [$startDate, $endDate])->get();
        
        $totalBudget = $projects->sum('budget');
        $totalSpent = $projects->sum('actual_cost');
        $totalRemaining = $totalBudget - $totalSpent;

        $expensesByCategory = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        $expensesByProject = Expense::where('status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('project')
            ->get()
            ->groupBy('project_id')
            ->map(function ($expenses) {
                return [
                    'project' => $expenses->first()->project,
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            });

        $stats = [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'utilization_rate' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
            'total_projects' => $projects->count(),
            'overbudget_projects' => $projects->filter(fn($p) => $p->isOverBudget())->count(),
        ];

        $pdf = Pdf::loadView('reports.pdf.financial', compact('stats', 'expensesByCategory', 'expensesByProject', 'startDate', 'endDate'));
        
        return $pdf->download('financial-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Display team performance report
     */
    public function team(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        // Team members with their tasks
        $teamMembers = User::where('role', 'team_member')
            ->withCount([
                'tasks as total_tasks',
                'tasks as completed_tasks' => fn($q) => $q->where('status', 'completed'),
                'tasks as in_progress_tasks' => fn($q) => $q->where('status', 'in_progress'),
                'tasks as overdue_tasks' => fn($q) => $q->where('status', '!=', 'completed')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()),
            ])
            ->with([
                'timeLogs' => fn($q) => $q->whereBetween('log_date', [$startDate, $endDate])
            ])
            ->get()
            ->map(function ($member) {
                $member->total_hours = $member->timeLogs->sum('hours');
                $member->completion_rate = $member->total_tasks > 0 
                    ? round(($member->completed_tasks / $member->total_tasks) * 100, 2) 
                    : 0;
                return $member;
            });

        // Top performers
        $topPerformers = $teamMembers->sortByDesc('completion_rate')->take(5);

        // Task distribution
        $taskDistribution = $teamMembers->map(function ($member) {
            return [
                'name' => $member->name,
                'tasks' => $member->total_tasks,
                'completed' => $member->completed_tasks,
            ];
        });

        return view('reports.team', compact('teamMembers', 'topPerformers', 'taskDistribution', 'startDate', 'endDate'));
    }

    /**
     * Export to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        $type = $request->input('type', 'projects');
        
        $filename = $type . '-report-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'projects') {
                // CSV Headers
                fputcsv($file, ['Project Code', 'Name', 'Manager', 'Client', 'Status', 'Priority', 'Budget', 'Actual Cost', 'Progress', 'Start Date', 'End Date']);

                // Data
                Project::with('manager', 'client')->chunk(100, function ($projects) use ($file) {
                    foreach ($projects as $project) {
                        fputcsv($file, [
                            $project->code,
                            $project->name,
                            $project->manager->name,
                            $project->client->name ?? 'N/A',
                            $project->status,
                            $project->priority,
                            $project->budget,
                            $project->actual_cost,
                            $project->progress . '%',
                            $project->start_date->format('Y-m-d'),
                            $project->end_date?->format('Y-m-d') ?? 'N/A',
                        ]);
                    }
                });
            } elseif ($type === 'tasks') {
                // CSV Headers
                fputcsv($file, ['Project', 'Task Title', 'Assigned To', 'Priority', 'Status', 'Start Date', 'Due Date', 'Estimated Hours', 'Actual Hours', 'Progress']);

                // Data
                Task::with('project', 'assignedUser')->chunk(100, function ($tasks) use ($file) {
                    foreach ($tasks as $task) {
                        fputcsv($file, [
                            $task->project->name,
                            $task->title,
                            $task->assignedUser->name ?? 'Unassigned',
                            $task->priority,
                            $task->status,
                            $task->start_date?->format('Y-m-d') ?? 'N/A',
                            $task->due_date?->format('Y-m-d') ?? 'N/A',
                            $task->estimated_hours ?? 0,
                            $task->actual_hours ?? 0,
                            $task->progress . '%',
                        ]);
                    }
                });
            } elseif ($type === 'expenses') {
                // CSV Headers
                fputcsv($file, ['Project', 'Title', 'Category', 'Amount', 'Status', 'Expense Date', 'Submitted By', 'Approved By']);

                // Data
                Expense::with('project', 'user', 'approver')->chunk(100, function ($expenses) use ($file) {
                    foreach ($expenses as $expense) {
                        fputcsv($file, [
                            $expense->project->name,
                            $expense->title,
                            $expense->category,
                            $expense->amount,
                            $expense->status,
                            $expense->expense_date->format('Y-m-d'),
                            $expense->user->name,
                            $expense->approver->name ?? 'N/A',
                        ]);
                    }
                });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}