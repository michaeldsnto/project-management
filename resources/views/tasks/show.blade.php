<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->title }}</h2>
                <p class="text-sm text-gray-500">{{ $task->project->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Task Details -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <div class="text-sm text-gray-500">Assigned To</div>
                        <div class="text-lg font-semibold">{{ $task->assignedUser->name ?? 'Unassigned' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Created By</div>
                        <div class="text-lg font-semibold">{{ $task->creator->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Priority</div>
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full
                            {{ $task->priority == 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $task->priority == 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $task->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $task->priority == 'low' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full
                            {{ $task->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $task->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $task->status == 'review' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $task->status == 'todo' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <div class="text-sm text-gray-500">Start Date</div>
                        <div class="font-semibold">{{ $task->start_date ? $task->start_date->format('M d, Y') : 'Not set' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Due Date</div>
                        <div class="font-semibold {{ $task->isOverdue() ? 'text-red-600' : '' }}">
                            {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No deadline' }}
                            @if($task->isOverdue())
                            <span class="text-xs text-red-500 ml-2">(Overdue)</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Progress</div>
                        <div class="flex items-center mt-1">
                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-indigo-600 h-2 rounded-full"  @style(['width' => ($project->progress ?? 0) . '%'])></div>
                            </div>
                            <span class="text-sm font-semibold">{{ $task->progress }}%</span>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                    <p class="text-gray-700">{{ $task->description ?? 'No description provided.' }}</p>
                </div>

                <div class="border-t pt-6 mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500">Estimated Hours</div>
                            <div class="text-2xl font-bold">{{ $task->estimated_hours ?? 0 }} hrs</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total Logged Hours</div>
                            <div class="text-2xl font-bold text-blue-600">{{ $task->total_logged_hours }} hrs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Time Logs -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Time Logs</h3>
                        <button onclick="document.getElementById('timeLogModal').classList.remove('hidden')" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm">
                            Log Time
                        </button>
                    </div>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($task->timeLogs as $log)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <div class="font-semibold text-sm">{{ $log->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $log->log_date->format('M d, Y') }}</div>
                                @if($log->description)
                                <div class="text-sm text-gray-600 mt-1">{{ $log->description }}</div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-indigo-600">{{ $log->hours }} hrs</div>
                                @if($log->user_id == auth()->id())
                                <form action="{{ route('time-logs.destroy', $log) }}" method="POST" class="mt-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">No time logs yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Attachments</h3>
                        <button onclick="document.getElementById('attachmentModal').classList.remove('hidden')" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-md text-sm">
                            Upload File
                        </button>
                    </div>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($task->attachments as $attachment)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <div class="font-semibold text-sm">{{ $attachment->file_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attachment->file_size_formatted }}</div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('attachments.download', $attachment) }}" 
                                   class="text-indigo-600 hover:text-indigo-800 text-sm">Download</a>
                                @if($attachment->uploaded_by == auth()->id())
                                <form action="{{ route('attachments.destroy', $attachment) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">No attachments yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Comments</h3>
                
                <!-- Add Comment Form -->
                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-6">
                    @csrf
                    <textarea name="content" rows="3" required placeholder="Add a comment..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Post Comment
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4">
                    @forelse($task->comments as $comment)
                    <div class="border-l-4 border-indigo-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-semibold mr-3">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">{{ $comment->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</div>
                                    <p class="text-gray-700 mt-1">{{ $comment->content }}</p>
                                </div>
                            </div>
                            @if($comment->user_id == auth()->id() || auth()->user()->isAdmin())
                            <form action="{{ route('comments.destroy', $comment) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">No comments yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Time Log Modal -->
    <div id="timeLogModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Log Time</h3>
            <form action="{{ route('tasks.time-logs.store', $task) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hours *</label>
                        <input type="number" name="hours" step="0.5" min="0.1" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="log_date" value="{{ date('Y-m-d') }}" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('timeLogModal').classList.add('hidden')"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Log Time
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attachment Modal -->
    <div id="attachmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Upload Attachment</h3>
            <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File *</label>
                        <input type="file" name="file" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Max file size: 10MB</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('attachmentModal').classList.add('hidden')"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>