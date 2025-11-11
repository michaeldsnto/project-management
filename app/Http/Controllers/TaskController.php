<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('project', 'assignedUser', 'creator')
            ->latest()
            ->paginate(15);
            
        return view('tasks.index', compact('tasks'));
    }

    public function create(Request $request)
    {
        $projectId = $request->query('project_id');
        $projects = Project::where('status', '!=', 'completed')->get();
        $users = User::where('role', '!=', 'client')
            ->where('is_active', true)
            ->get();
        
        return view('tasks.create', compact('projects', 'users', 'projectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
        ]);

        $validated['created_by'] = Auth::id();

        $task = Task::create($validated);

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Task created successfully!');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignedUser', 'creator', 'comments.user', 'attachments', 'timeLogs.user']);
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $projects = Project::where('status', '!=', 'completed')->get();
        $users = User::where('role', '!=', 'client')
            ->where('is_active', true)
            ->get();
        
        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|integer|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        // Auto-complete if progress is 100
        if (isset($validated['progress']) && $validated['progress'] == 100) {
            $validated['status'] = 'completed';
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $projectId = $task->project_id;
        $task->delete();

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task deleted successfully!');
    }

    // Update task status via AJAX
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
        ]);

        $task->update($validated);

        if ($validated['status'] === 'completed') {
            $task->markAsCompleted();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task status updated!',
        ]);
    }
}
