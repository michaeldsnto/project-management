<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Authenticated & Active User Routes
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    
    // Dashboard - All authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects Routes
    Route::middleware(['log.activity'])->group(function () {
        
        // List & View Projects (All users)
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])
            ->middleware('project.access')
            ->name('projects.show');

        // Create, Update, Delete Projects (Admin & PM only)
        Route::middleware(['role:admin,project_manager'])->group(function () {
            Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
            Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
                ->middleware('project.access')
                ->name('projects.edit');
            Route::put('/projects/{project}', [ProjectController::class, 'update'])
                ->middleware('project.access')
                ->name('projects.update');
            Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
                ->middleware('project.access')
                ->name('projects.destroy');
        });

        // Team Members Management (Admin & PM only)
        Route::middleware(['role:admin,project_manager', 'project.access'])->group(function () {
            Route::post('/projects/{project}/team-members', [TeamMemberController::class, 'store'])
                ->name('projects.team-members.store');
            Route::patch('/projects/{project}/team-members/{teamMember}', [TeamMemberController::class, 'update'])
                ->name('projects.team-members.update');
            Route::delete('/projects/{project}/team-members/{teamMember}', [TeamMemberController::class, 'destroy'])
                ->name('projects.team-members.destroy');
        });

        // Reports (Admin & PM only)
        Route::middleware(['role:admin,project_manager'])->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/project/{project}', [ReportController::class, 'project'])->name('project');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/team', [ReportController::class, 'team'])->name('team');
            Route::get('/export/project/{project}', [ReportController::class, 'exportProject'])->name('export.project');
            Route::get('/export/financial', [ReportController::class, 'exportFinancial'])->name('export.financial');
            Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        });

        // Expenses Routes
        Route::middleware(['project.access'])->group(function () {
            Route::get('/projects/{project}/expenses', [ExpenseController::class, 'index'])
                ->name('projects.expenses.index');
            
            // Create expense (Not for clients)
            Route::post('/projects/{project}/expenses', [ExpenseController::class, 'store'])
                ->middleware('not.client')
                ->name('projects.expenses.store');
        });

        // Expense Approval (Admin & PM only)
        Route::middleware(['role:admin,project_manager'])->group(function () {
            Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])
                ->name('expenses.approve');
            Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject'])
                ->name('expenses.reject');
        });

        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
            ->name('expenses.destroy');
    });

    // Tasks Routes
    Route::middleware(['log.activity', 'not.client'])->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        
        Route::middleware('task.access')->group(function () {
            Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
            Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
            Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
            Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
            Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
            
            // Comments
            Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');
            
            // Attachments
            Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('tasks.attachments.store');
            
            // Time Logs
            Route::post('/tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
        });
    });

    // Comments, Attachments, Time Logs Delete
    Route::middleware(['log.activity'])->group(function () {
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
        Route::delete('/time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');
    });

    // Reports (Admin & PM only)
    Route::middleware(['role:admin,project_manager'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/project/{project}', function () { return view('reports.project'); })->name('project');
        Route::get('/financial', function () { return view('reports.financial'); })->name('financial');
    });
});

require __DIR__.'/auth.php';