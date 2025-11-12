<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>
            <a href="{{ route('projects.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Create Project
            </a>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($projects as $project)
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $project->code }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $project->priority == 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $project->priority == 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $project->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $project->priority == 'low' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($project->priority) }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $project->description }}</p>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Manager:</span>
                            <span class="font-medium">{{ $project->manager->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Budget:</span>
                            <span class="font-medium">Rp {{ number_format($project->budget, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Progress</span>
                            <span class="font-medium">{{ $project->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full"  @style(['width' => ($project->progress ?? 0) . '%'])></div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('projects.show', $project) }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                            View Details
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            Edit
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-500 mb-4">No projects found</p>
                    <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        Create your first project
                    </a>
                </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</x-app-layout>