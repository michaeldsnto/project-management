<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->name }}</h2>
                <p class="text-sm text-gray-500">{{ $project->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Project Overview -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <div class="text-sm text-gray-500">Manager</div>
                        <div class="text-lg font-semibold">{{ $project->manager->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Client</div>
                        <div class="text-lg font-semibold">{{ $project->client->name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full
                            {{ $project->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $project->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $project->status == 'planning' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Priority</div>
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full
                            {{ $project->priority == 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $project->priority == 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $project->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $project->priority == 'low' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($project->priority) }}
                        </span>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                    <p class="text-gray-700">{{ $project->description ?? 'No description provided.' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Tasks</div>
                <div class="text-3xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Completed Tasks</div>
                <div class="text-3xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Expenses</div>
                <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($stats['total_expenses'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Team Size</div>
                <div class="text-3xl font-bold text-indigo-600">{{ $stats['team_size'] }}</div>
            </div>
        </div>

        <!-- Budget Overview -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Budget Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full {{ $project->isOverBudget() ? 'bg-red-600' : '' }}" 
                             @style(['width' =>  min(($project->actual_cost / $project->budget) * 100, 100) . '%'])></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('tasks')" id="tab-tasks" class="tab-button active px-6 py-3 text-sm font-medium border-b-2">
                        Tasks ({{ $project->tasks->count() }})
                    </button>
                    <button onclick="showTab('team')" id="tab-team" class="tab-button px-6 py-3 text-sm font-medium border-b-2">
                        Team ({{ $project->teamMembers->count() }})
                    </button>
                    <button onclick="showTab('expenses')" id="tab-expenses" class="tab-button px-6 py-3 text-sm font-medium border-b-2">
                        Expenses ({{ $project->expenses->count() }})
                    </button>
                </nav>
            </div>

            <!-- Tasks Tab -->
            <div id="content-tasks" class="tab-content p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-semibold">Project Tasks</h3>
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Add Task
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($project->tasks as $task)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $task->title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
                                <div class="flex gap-3 mt-2 text-xs text-gray-500">
                                    <span>Assigned: {{ $task->assignedUser->name ?? 'Unassigned' }}</span>
                                    <span>Due: {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No deadline' }}</span>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $task->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $task->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $task->status == 'todo' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                                <div class="mt-2">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-8">No tasks yet. Create your first task!</p>
                    @endforelse
                </div>
            </div>

            <!-- Team Tab -->
            <div id="content-team" class="tab-content hidden p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-semibold">Team Members</h3>
                    <button onclick="document.getElementById('addTeamModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Add Member
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse($project->teamMembers as $member)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-semibold">
                                {{ substr($member->name, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <div class="font-semibold text-gray-900">{{ $member->name }}</div>
                                <div class="text-sm text-gray-500">{{ $member->pivot->role }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">{{ $member->pivot->allocation_percentage }}% allocated</div>
                            <div class="text-xs text-gray-500">Joined: {{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-8">No team members yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Expenses Tab -->
            <div id="content-expenses" class="tab-content hidden p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-semibold">Project Expenses</h3>
                    <a href="{{ route('projects.expenses.index', $project) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Manage Expenses
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($project->expenses->take(10) as $expense)
                    <div class="flex justify-between items-center p-4 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $expense->title }}</div>
                            <div class="text-sm text-gray-500">{{ $expense->category }} • {{ $expense->expense_date->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</div>
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $expense->status == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $expense->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $expense->status == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($expense->status) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-8">No expenses recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Remove active from all buttons
            document.querySelectorAll('.tab-button').forEach(el => {
                el.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });
            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            // Add active to selected button
            const btn = document.getElementById('tab-' + tabName);
            btn.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            btn.classList.remove('border-transparent', 'text-gray-500');
        }
    </script>
</x-app-layout>