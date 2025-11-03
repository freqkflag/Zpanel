@php
    /** @var App\Models\APIService $service */
@endphp

<x-layouts.app>
    <div class="p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $service->name }} - Analytics
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Service metrics and performance analytics
                </p>
            </div>
            <a href="{{ route('api-gateway.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                Back to Services
            </a>
        </div>

        @livewire('api-gateway.service-analytics', ['service' => $service])

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Requests Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Requests
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($service->requests_count ?? 0) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Time Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Avg Response Time
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($service->avg_response_time ?? 0, 2) }}ms
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Rate Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Error Rate
                                </dt>
                                <dd class="text-lg font-semibold text-red-600 dark:text-red-400">
                                    {{ number_format($service->error_rate ?? 0, 2) }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="mt-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Request Volume (Last 7 Days)
                </h2>
                <div class="h-64 flex items-center justify-center text-gray-400 dark:text-gray-600">
                    <!-- Chart would be rendered here with Chart.js or similar -->
                    <p>Chart visualization coming soon</p>
                </div>
            </div>
        </div>

        <!-- Status Codes Breakdown -->
        <div class="mt-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Status Code Distribution
                </h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $service->status_2xx ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">2xx Success</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ $service->status_4xx ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">4xx Client Errors</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ $service->status_5xx ?? 0 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">5xx Server Errors</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

