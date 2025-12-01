<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <!-- LEFT: Logo + Nav Links -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                    ProjectHub
                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex space-x-8">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2
                        {{ request()->routeIs('dashboard') ? 'border-indigo-4   00 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }}
                        text-sm font-medium">
                        Dashboard
                    </a>

                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2
                        {{ request()->routeIs('projects.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }}
                        text-sm font-medium">
                        Projects
                    </a>

                    <a href="{{ route('tasks.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2
                        {{ request()->routeIs('tasks.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }}
                        text-sm font-medium">
                        Tasks
                    </a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isProjectManager())
                    <a href="{{ route('reports.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2
                        {{ request()->routeIs('reports.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }}
                        text-sm font-medium">
                        Reports
                    </a>
                    @endif
                </div>
            </div>

            <!-- RIGHT: User Info -->
            <div class="hidden sm:flex items-center space-x-4">

                <div class="flex items-center text-sm text-gray-600 space-x-2">
                    <span class="font-medium">{{ Auth::user()->name }}</span>
                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-gray-500 hover:text-gray-700">
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</nav>
