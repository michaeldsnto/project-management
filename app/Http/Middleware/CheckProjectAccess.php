<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    /**
     * Handle an incoming request.
     * Check if user has access to the project
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $projectId = $request->route('project')?->id ?? $request->route('project');

        if (!$projectId) {
            return $next($request);
        }

        $project = Project::find($projectId);

        if (!$project) {
            abort(404, 'Project not found.');
            return response('Project not found', 404);
        }

        // Admin has access to all projects
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        // Project manager has access if they manage the project
        if ($user && method_exists($user, 'isProjectManager') && $project->manager_id === $user->id) {
            return $next($request);
        }

        // Check if user is a team member of the project
        $isTeamMember = $project->teamMembers()
            ->where('user_id', $user->id)
            ->exists();

        // Check if user is the client
        $isClient = $project->client_id === $user->id;

        if ($isTeamMember || $isClient) {
            return $next($request);
        }

        abort(403, 'You do not have access to this project.');
        return response('Forbidden', 403); // appease Intelephense
    }
}
