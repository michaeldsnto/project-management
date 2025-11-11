<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Invoice::with('project', 'client');

        // Filter based on user role
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            // Clients can only see their own invoices
            if ($user->isClient()) {
                $query->where('client_id', $user->id);
            } else {
                // Team members can see invoices for projects they're on
                $projectIds = $user->teamProjects()->pluck('projects.id');
                $query->whereIn('project_id', $projectIds);
            }
        }

        // Apply filters
        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('project_id')) {
            $query->where('project_id', request('project_id'));
        }

        if (request('date_from')) {
            $query->where('issue_date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->where('issue_date', '<=', request('date_to'));
        }

        $invoices = $query->latest()->paginate(15);

        // Get filter options
        $projects = Project::select('id', 'name')->get();
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

        return view('invoices.index', compact('invoices', 'projects', 'statuses'));
    }

    public function create()
    {
        $user = Auth::user();

        // Only admins and project managers can create invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        $projects = Project::with('client')->get();
        $clients = User::where('role', 'client')->where('is_active', true)->get();

        return view('invoices.create', compact('projects', 'clients'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Only admins and project managers can create invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Generate invoice number
        $validated['invoice_number'] = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);

        // Calculate total
        $validated['total'] = $validated['subtotal'] + ($validated['tax'] ?? 0) - ($validated['discount'] ?? 0);

        Invoice::create($validated);

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully!');
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            if ($user->isClient() && $invoice->client_id !== $user->id) {
                abort(403);
            } elseif (!$user->teamProjects()->where('projects.id', $invoice->project_id)->exists()) {
                abort(403);
            }
        }

        $invoice->load('project', 'client');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $user = Auth::user();

        // Only admins and project managers can edit invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        // Can only edit draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be edited.');
        }

        $projects = Project::with('client')->get();
        $clients = User::where('role', 'client')->where('is_active', true)->get();

        return view('invoices.edit', compact('invoice', 'projects', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Only admins and project managers can update invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        // Can only edit draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate total
        $validated['total'] = $validated['subtotal'] + ($validated['tax'] ?? 0) - ($validated['discount'] ?? 0);

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully!');
    }

    public function destroy(Invoice $invoice)
    {
        $user = Auth::user();

        // Only admins and project managers can delete invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        // Can only delete draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully!');
    }

    public function send(Invoice $invoice)
    {
        $user = Auth::user();

        // Only admins and project managers can send invoices
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        // Update status to sent
        $invoice->update(['status' => 'sent']);

        // TODO: Send email notification to client
        // Mail::to($invoice->client->email)->send(new InvoiceSent($invoice));

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice sent successfully!');
    }

    public function markPaid(Invoice $invoice)
    {
        $user = Auth::user();

        // Only admins and project managers can mark as paid
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            abort(403);
        }

        $invoice->markAsPaid();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice marked as paid!');
    }

    public function download(Invoice $invoice)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isAdmin() && !$user->isProjectManager()) {
            if ($user->isClient() && $invoice->client_id !== $user->id) {
                abort(403);
            } elseif (!$user->teamProjects()->where('projects.id', $invoice->project_id)->exists()) {
                abort(403);
            }
        }

        // TODO: Generate PDF invoice
        // For now, return JSON
        $data = [
            'invoice' => $invoice->load('project', 'client')->toArray(),
        ];

        return response()->json($data);
    }
}
