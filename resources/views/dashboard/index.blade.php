<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($stats as $label => $value)
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-500">
                                {{ ucwords(str_replace('_', ' ', $label)) }}
                            </div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ is_numeric($value) && $label != 'total_expenses' ? $value : 'Rp ' . number_format($value, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Recent Projects -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Projects</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentProjects as $project)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $project->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $project->manager->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $project->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $project->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $project->status == 'planning' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-indigo-600 h-2 rounded-full"  @style(['width' => ($project->progress ?? 0) . '%'])></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $project->progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No projects found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Deadlines</h3>
                <div class="space-y-3">
                    @forelse($upcomingDeadlines as $task)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                            <div class="text-xs text-gray-500">{{ $task->project->name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm {{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ $task->due_date->format('M d, Y') }}
                            </div>
                            @if($task->isOverdue())
                            <span class="text-xs text-red-500">Overdue!</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">No upcoming deadlines</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>