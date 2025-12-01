<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
     public function store(CommentRequest $request, Task $task)
    {
        $validated = $request->validated();
        $validated['task_id'] = $task->id;
        $validated['user_id'] = Auth::id();

        Comment::create($validated);

        return back()->with('success', 'Comment added!');
    }

    public function destroy(Comment $comment)
    {
        // Only owner or admin can delete
        if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }
}