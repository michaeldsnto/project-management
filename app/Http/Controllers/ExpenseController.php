<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Project $project)
    {
        $expenses = $project->expenses()
            ->with('user', 'approver')
            ->latest()
            ->paginate(15);
            
        return view('expenses.index', compact('project', 'expenses'));
    }

    public function store(ExpenseRequest $request, Project $project)
    {
        $validated = $request->validated();
        $validated['project_id'] = $project->id;
        $validated['user_id'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $validated['receipt'] = $path;
        }

        Expense::create($validated);

        return back()->with('success', 'Expense added successfully!');
    }

    public function approve(Expense $expense)
    {
        $expense->approve(Auth::id());

        // Update project actual cost
        $expense->project->increment('actual_cost', (float) $expense->amount);

        return back()->with('success', 'Expense approved!');
    }

    public function reject(Expense $expense)
    {
        $expense->update(['status' => 'rejected']);

        return back()->with('success', 'Expense rejected!');
    }

    public function destroy(Expense $expense)
    {
        // Delete receipt file if exists
        if ($expense->receipt) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $expense->delete();

        return back()->with('success', 'Expense deleted successfully!');
    }
}
