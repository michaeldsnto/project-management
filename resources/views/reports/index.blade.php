<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports & Analytics</h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Projects</div>
                <div class="text-3xl font-bold text-gray-900">{{ $stats['total_projects'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Active Projects</div>
                <div class="text-3xl font-bold text-blue-600">{{ $stats['active_projects'] }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Budget</div>
                <div class="text-3xl font-bold text-green-600">Rp {{ number_format($stats['total_budget'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-sm text-gray-500">Total Spent</div>
                <div class="text-3xl font-bold text-red-600">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Report Types -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Project Reports -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Project Reports</h3>
                <p class="text-gray-600 text-sm mb-4">Generate detailed reports for individual projects including tasks, expenses, and team performance.</p>
                <select id="projectSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 mb-4">
                    <option value="">Select a project</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button onclick="viewProjectReport()" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        View Report
                    </button>
                    <button onclick="downloadProjectReport()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
                        PDF
                    </button>
                </div>
            </div>

            <!-- Financial Reports -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Financial Reports</h3>
                <p class="text-gray-600 text-sm mb-4">Analyze budget utilization, expenses breakdown, and financial performance across all projects.</p>
                <div class="space-y-3 mb-4">
                    <input type="date" id="finStartDate" class="w-full border border-gray-300 rounded-md px-3 py-2" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    <input type="date" id="finEndDate" class="w-full border border-gray-300 rounded-md px-3 py-2" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('reports.financial') }}" class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        View Report
                    </a>
                    <button onclick="downloadFinancialReport()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">
                        PDF
                    </button>
                </div>
            </div>

            <!-- Export Data -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Export Data</h3>
                <p class="text-gray-600 text-sm mb-4">Export projects, tasks, or expenses data to Excel (CSV) format for further analysis.</p>
                <div class="space-y-2">
                    <a href="{{ route('reports.export.excel', ['type' => 'projects']) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm text-center">
                        Export Projects
                    </a>
                    <a href="{{ route('reports.export.excel', ['type' => 'tasks']) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm text-center">
                        Export Tasks
                    </a>
                    <a href="{{ route('reports.export.excel', ['type' => 'expenses']) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm text-center">
                        Export Expenses
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Task Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-500">Total Tasks</div>
                    <div class="text-2xl font-bold">{{ $stats['total_tasks'] }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Completed</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['completed_tasks'] }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $stats['total_tasks'] > 0 ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1) : 0 }}% completion rate
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">In Progress</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_tasks'] - $stats['completed_tasks'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewProjectReport() {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                window.location.href = `/reports/project/${projectId}`;
            } else {
                alert('Please select a project');
            }
        }

        function downloadProjectReport() {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                window.location.href = `/reports/export/project/${projectId}`;
            } else {
                alert('Please select a project');
            }
        }

        function downloadFinancialReport() {
            const startDate = document.getElementById('finStartDate').value;
            const endDate = document.getElementById('finEndDate').value;
            window.location.href = `/reports/export/financial?start_date=${startDate}&end_date=${endDate}`;
        }
    </script>
</x-app-layout>