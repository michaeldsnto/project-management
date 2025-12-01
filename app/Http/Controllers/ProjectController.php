<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
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

    public function store(ProjectRequest $request)
    {
        $validated = $request->validated();
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

    public function update(ProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

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