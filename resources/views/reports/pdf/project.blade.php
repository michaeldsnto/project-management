<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Report - {{ $project->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 16px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 30%; font-weight: bold; padding: 5px; }
        .info-value { display: table-cell; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .stats-box { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .stats-grid { display: table; width: 100%; }
        .stat-item { display: table-cell; text-align: center; padding: 10px; }
        .stat-value { font-size: 24px; font-weight: bold; }
        .stat-label { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Report</h1>
        <p>{{ $project->name }} ({{ $project->code }})</p>
        <p>Generated on {{ now()->format('F d, Y H:i') }}</p>
    </div>

    <div class="section">
        <h2>Project Information</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Project Name:</div>
                <div class="info-value">{{ $project->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Project Code:</div>
                <div class="info-value">{{ $project->code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Manager:</div>
                <div class="info-value">{{ $project->manager->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Client:</div>
                <div class="info-value">{{ $project->client->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Priority:</div>
                <div class="info-value">{{ ucfirst($project->priority) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Period:</div>
                <div class="info-value">{{ $project->start_date->format('M d, Y') }} - {{ $project->end_date?->format('M d, Y') ?? 'Ongoing' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Project Statistics</h2>
        <div class="stats-box">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['total_tasks'] }}</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['completed_tasks'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $stats['team_size'] }}</div>
                    <div class="stat-label">Team Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($project->progress) }}%</div>
                    <div class="stat-label">Progress</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Budget Overview</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Total Budget:</div>
                <div class="info-value">Rp {{ number_format($project->budget, 0, ',', '.') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Actual Cost:</div>
                <div class="info-value">Rp {{ number_format($project->actual_cost, 0, ',', '.') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Remaining:</div>
                <div class="info-value">Rp {{ number_format($project->budget_remaining, 0, ',', '.') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Utilization:</div>
                <div class="info-value">{{ round($stats['budget_utilization'], 2) }}%</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Tasks Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->tasks->take(20) as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->assignedUser->name ?? 'Unassigned' }}</td>
                    <td>{{ ucfirst($task->priority) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                    <td>{{ $task->progress }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Team Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Allocation</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->teamMembers as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ ucfirst($member->pivot->role) }}</td>
                    <td>{{ $member->pivot->allocation_percentage }}%</td>
                    <td>{{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Expenses Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->expenses->take(15) as $expense)
                <tr>
                    <td>{{ $expense->title }}</td>
                    <td>{{ ucfirst($expense->category) }}</td>
                    <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold;">
                    <td colspan="2">Total Approved Expenses</td>
                    <td colspan="2">Rp {{ number_format($stats['total_expenses'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>