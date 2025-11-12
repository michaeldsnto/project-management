<?php

// ==============================================
// File: routes/web.php
// ==============================================

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Projects Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('projects', ProjectController::class);
    
    // Team Members Management (nested under projects)
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::post('team-members', [TeamMemberController::class, 'store'])
            ->name('team-members.store');
        Route::patch('team-members/{teamMember}', [TeamMemberController::class, 'update'])
            ->name('team-members.update');
        Route::delete('team-members/{teamMember}', [TeamMemberController::class, 'destroy'])
            ->name('team-members.destroy');
            
        // Expenses Management (nested under projects)
        Route::get('expenses', [ExpenseController::class, 'index'])
            ->name('expenses.index');
        Route::post('expenses', [ExpenseController::class, 'store'])
            ->name('expenses.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Tasks Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('tasks', TaskController::class);
    
    // AJAX route for updating task status
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.update-status');
    
    // Comments (nested under tasks)
    Route::post('tasks/{task}/comments', [CommentController::class, 'store'])
        ->name('tasks.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
    
    // Attachments (nested under tasks)
    Route::post('tasks/{task}/attachments', [AttachmentController::class, 'store'])
        ->name('tasks.attachments.store');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy');
    
    // Time Logs (nested under tasks)
    Route::post('tasks/{task}/time-logs', [TimeLogController::class, 'store'])
        ->name('tasks.time-logs.store');
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])
        ->name('time-logs.destroy');

    /*
    |--------------------------------------------------------------------------
    | Expenses Routes
    |--------------------------------------------------------------------------
    */
    Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])
        ->name('expenses.approve')
        ->middleware('role:admin,project_manager');
    Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])
        ->name('expenses.reject')
        ->middleware('role:admin,project_manager');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy');

    /*
    |--------------------------------------------------------------------------
    | Reports Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/project/{project}', [ReportController::class, 'project'])->name('project');
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/team', [ReportController::class, 'team'])->name('team');
        Route::get('/export/project/{project}', [ReportController::class, 'exportProject'])->name('export.project');
        Route::get('/export/financial', [ReportController::class, 'exportFinancial'])->name('export.financial');
    });

    /*
    |--------------------------------------------------------------------------
    | Invoices Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])
        ->name('invoices.send');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
        ->name('invoices.mark-paid');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download');

    /*
    |--------------------------------------------------------------------------
    | User Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes (for AJAX requests)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    // Get project statistics
    Route::get('projects/{project}/stats', function ($projectId) {
        $project = \App\Models\Project::with(['tasks', 'expenses'])->findOrFail($projectId);
        
        return response()->json([
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'progress' => $project->calculateProgress(),
            'budget_used' => $project->actual_cost,
            'budget_remaining' => $project->budget_remaining,
        ]);
    })->name('projects.stats');
    
    // Get task statistics
    Route::get('tasks/{task}/stats', function ($taskId) {
        $task = \App\Models\Task::with(['timeLogs', 'comments'])->findOrFail($taskId);
        
        return response()->json([
            'total_hours' => $task->total_logged_hours,
            'comments_count' => $task->comments->count(),
            'is_overdue' => $task->isOverdue(),
        ]);
    })->name('tasks.stats');
    
    // Search users for assignment
    Route::get('users/search', function (\Illuminate\Http\Request $request) {
        $query = $request->get('q');
        
        $users = \App\Models\User::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'name', 'email', 'avatar']);
        
        return response()->json($users);
    })->name('users.search');
});

// Authentication routes (provided by Laravel Breeze)
require __DIR__.'/auth.php';