
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Financial Report</h2>
            <a href="{{ route('reports.export.financial', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                Download PDF
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('reports.financial') }}" class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="border border-gray-300 rounded-md px-3 py-2">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                    Apply Filter
                </button>
            </form>
        </div>

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Budget</div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_budget'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Spent</div>
                <div class="text-2xl font-bold text-red-600">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Remaining Budget</div>
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_remaining'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Utilization Rate</div>
                <div class="text-2xl font-bold text-blue-600">{{ round($stats['utilization_rate'], 1) }}%</div>
            </div>
        </div>

        <!-- Project Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Project Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-500">Total Projects</div>
                    <div class="text-3xl font-bold">{{ $stats['total_projects'] }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Over-budget Projects</div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['overbudget_projects'] }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Average Utilization</div>
                    <div class="text-3xl font-bold">{{ round($stats['utilization_rate'], 1) }}%</div>
                </div>
            </div>
        </div>

        <!-- Expenses by Category -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Expenses by Category</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $totalExpenses = $expensesByCategory->sum(); @endphp
                        @foreach($expensesByCategory as $category => $amount)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ ucfirst($category) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2" style="width: 100px;">
                                        <div class="bg-indigo-600 h-2 rounded-full" 
                                             style="width: {{ $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0 }}%"></div>
                                    </div>
                                    {{ $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 1) : 0 }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expenses by Project -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Expenses by Project</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Expenses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number of Expenses</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($expensesByProject as $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('projects.show', $data['project']) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $data['project']->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rp {{ number_format($data['total'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $data['count'] }} expenses
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>