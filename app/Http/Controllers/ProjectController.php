<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('manager', 'client', 'teamMembers')
            ->latest()
            ->paginate(10);
            
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $managers = User::where('role', 'project_manager')
            ->orWhere('role', 'admin')
            ->get();
            
        $clients = User::where('role', 'client')->get();
        
        return view('projects.create', compact('managers', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        // Generate unique project code
        $validated['code'] = 'PRJ-' . strtoupper(Str::random(6));
        
        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'client', 'teamMembers', 'tasks.assignedUser', 'expenses']);
        
        // Calculate statistics
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'total_expenses' => $project->expenses->where('status', 'approved')->sum('amount'),
            'team_size' => $project->teamMembers->count(),
        ];
        
        return view('projects.show', compact('project', 'stats'));
    }

    public function edit(Project $project)
    {
        $managers = User::where('role', 'project_manager')
            ->orWhere('role', 'admin')
            ->get();
            
        $clients = User::where('role', 'client')->get();
        
        return view('projects.edit', compact('project', 'managers', 'clients'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully!');
    }
}