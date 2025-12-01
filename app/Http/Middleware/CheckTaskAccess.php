<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTaskAccess
{
    /**
     * Handle an incoming request.
     * Check if user has access to the task
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $taskId = $request->route('task')?->id ?? $request->route('task');

        if (!$taskId) {
            return $next($request);
        }

        $task = Task::with('project')->find($taskId);

        if (!$task) {
            abort(404, 'Task not found.');
            return response('Task not found', 404); // For Intelephense peace of mind
        }

        // Admin has access to all tasks
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $project = $task->project;

        // Project manager has access if they manage the project
        if ($user && method_exists($user, 'isProjectManager') && $project && $project->manager_id === $user->id) {
            return $next($request);
        }

        // Task creator has access
        if ($user && $task->created_by === $user->id) {
            return $next($request);
        }

        // Assigned user has access
        if ($user && $task->assigned_to === $user->id) {
            return $next($request);
        }

        // Team members of the project have access
        $isTeamMember = $project && $project->teamMembers()
            ->where('user_id', $user->id)
            ->exists();

        if ($isTeamMember) {
            return $next($request);
        }

        abort(403, 'You do not have access to this task.');
        return response('Forbidden', 403); // keeps Intelephense from crying
    }
}
