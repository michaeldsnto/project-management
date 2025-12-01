<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 16px; border-bottom: 2px solid #333; padding-bottom: 5px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 40%; font-weight: bold; padding: 5px; }
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
        <h1>Financial Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
        <p>Generated on {{ now()->format('F d, Y H:i') }}</p>
    </div>

    <div class="section">
        <h2>Financial Overview</h2>
        <div class="stats-box">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">Rp {{ number_format($stats['total_budget'] / 1000000, 1) }}M</div>
                    <div class="stat-label">Total Budget</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">Rp {{ number_format($stats['total_spent'] / 1000000, 1) }}M</div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">Rp {{ number_format($stats['total_remaining'] / 1000000, 1) }}M</div>
                    <div class="stat-label">Remaining</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ round($stats['utilization_rate'], 1) }}%</div>
                    <div class="stat-label">Utilization Rate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Summary Statistics</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Total Projects:</div>
                <div class="info-value">{{ $stats['total_projects'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Over-budget Projects:</div>
                <div class="info-value">{{ $stats['overbudget_projects'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Budget Utilization:</div>
                <div class="info-value">{{ round($stats['utilization_rate'], 2) }}%</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Expenses by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php $totalExpenses = $expensesByCategory->sum(); @endphp
                @foreach($expensesByCategory as $category => $amount)
                <tr>
                    <td>{{ ucfirst($category) }}</td>
                    <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                    <td>{{ $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 2) : 0 }}%</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold;">
                    <td>Total</td>
                    <td>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Expenses by Project</h2>
        <table>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Total Expenses</th>
                    <th>Number of Expenses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByProject as $data)
                <tr>
                    <td>{{ $data['project']->name }}</td>
                    <td>Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                    <td>{{ $data['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>