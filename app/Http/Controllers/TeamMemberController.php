<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:lead,developer,designer,tester,analyst',
            'allocation_percentage' => 'required|integer|min:0|max:100',
            'joined_at' => 'required|date',
        ]);

        $validated['project_id'] = $project->id;

        // Check if user already in project
        $exists = TeamMember::where('user_id', $validated['user_id'])
            ->where('project_id', $project->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already in this project!');
        }

        TeamMember::create($validated);

        return back()->with('success', 'Team member added successfully!');
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $validated = $request->validate([
            'role' => 'required|in:lead,developer,designer,tester,analyst',
            'allocation_percentage' => 'required|integer|min:0|max:100',
        ]);

        $teamMember->update($validated);

        return back()->with('success', 'Team member updated successfully!');
    }

    public function destroy(TeamMember $teamMember)
    {
        $teamMember->update([
            'is_active' => false,
            'left_at' => now(),
        ]);

        return back()->with('success', 'Team member removed from project!');
    }
}
