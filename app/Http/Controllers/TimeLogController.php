<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.1|max:24',
            'log_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['task_id'] = $task->id;
        $validated['user_id'] = Auth::id();

        TimeLog::create($validated);

        // Update task actual hours using update instead of increment
        $task->update(['actual_hours' => $task->actual_hours + (float) $validated['hours']]);

        return back()->with('success', 'Time log added!');
    }

    public function destroy(TimeLog $timeLog)
    {
        // Update task actual hours using update instead of decrement
        $timeLog->task->update(['actual_hours' => $timeLog->task->actual_hours - (float) $timeLog->hours]);

        $timeLog->delete();

        return back()->with('success', 'Time log deleted!');
    }
}
