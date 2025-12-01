<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
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

    public function store(TaskRequest $request)
    {
        $validated = $request->validated();
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

    public function update(TaskRequest $request, Task $task)
    {
        $task->update($request->validated());

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
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $validated = $request->validated();

        if ($validated['status'] === 'completed') {
            $task->markAsCompleted();
        } else {
            $task->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task status updated!',
        ]);
    }
}
