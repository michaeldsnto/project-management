<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">All Tasks</h2>
            <a href="{{ route('tasks.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Create Task
            </a>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-6">
            <!-- Filter Buttons -->
            <div class="flex gap-2 mb-6">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">All</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">My Tasks</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Pending</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">In Progress</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Completed</button>
            </div>

            <!-- Tasks Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($task->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $task->project->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $task->assignedUser->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $task->priority == 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $task->priority == 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $task->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $task->priority == 'low' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $task->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $task->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $task->status == 'review' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $task->status == 'todo' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No deadline' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No tasks found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>