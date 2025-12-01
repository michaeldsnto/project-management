<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project Report</h2>
                <p class="text-sm text-gray-500">{{ $project->name }}</p>
            </div>
            <a href="{{ route('reports.export.project', $project) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                Download PDF
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Project Info Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Project Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Project Code</div>
                    <div class="font-semibold">{{ $project->code }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Manager</div>
                    <div class="font-semibold">{{ $project->manager->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Client</div>
                    <div class="font-semibold">{{ $project->client->name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $project->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $project->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $project->status == 'planning' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Period</div>
                    <div class="font-semibold">{{ $project->start_date->format('M d, Y') }} - {{ $project->end_date?->format('M d, Y') ?? 'Ongoing' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Priority</div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $project->priority == 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $project->priority == 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                        {{ $project->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $project->priority == 'low' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ ucfirst($project->priority) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Tasks</div>
                <div class="text-3xl font-bold">{{ $stats['total_tasks'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Completed Tasks</div>
                <div class="text-3xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Team Size</div>
                <div class="text-3xl font-bold text-indigo-600">{{ $stats['team_size'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Progress</div>
                <div class="text-3xl font-bold text-blue-600">{{ round($project->progress) }}%</div>
            </div>
        </div>

        <!-- Budget Overview -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Budget Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <div class="text-sm text-gray-500">Total Budget</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($project->budget, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Actual Cost</div>
                    <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($project->actual_cost, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Remaining</div>
                    <div class="text-2xl font-bold {{ $project->isOverBudget() ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format($project->budget_remaining, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Utilization</div>
                    <div class="text-2xl font-bold">{{ round($stats['budget_utilization'], 2) }}%</div>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $project->isOverBudget() ? 'bg-red-600' : 'bg-blue-600' }}" 
                         style="width: {{ min($stats['budget_utilization'], 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Tasks Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Tasks by Status</h3>
                <div class="space-y-3">
                    @foreach($tasksByStatus as $status => $count)
                    <div class="flex justify-between items-center">
                        <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                        <span class="font-semibold">{{ $count }} tasks</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Tasks by Priority</h3>
                <div class="space-y-3">
                    @foreach($tasksByPriority as $priority => $count)
                    <div class="flex justify-between items-center">
                        <span class="text-sm">{{ ucfirst($priority) }}</span>
                        <span class="font-semibold">{{ $count }} tasks</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Expenses Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Expenses by Category</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($expensesByCategory as $category => $amount)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="text-sm text-gray-500">{{ ucfirst($category) }}</div>
                    <div class="text-xl font-bold">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $stats['total_expenses'] > 0 ? round(($amount / $stats['total_expenses']) * 100, 1) : 0 }}% of total
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Team Members -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Team Members</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allocation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($project->teamMembers as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $member->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst($member->pivot->role) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->pivot->allocation_percentage }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>