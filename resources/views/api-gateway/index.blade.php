<x-layout>
    <x-slot:title>
        API Gateway | Zpanel
    </x-slot>
    <div class="flex items-center gap-2">
        <h1>API Gateway</h1>
        <a href="{{ route('api-gateway.create') }}">
            <x-forms.button>+ Add Service</x-forms.button>
        </a>
    </div>
    <div class="subtitle">Manage API services with Kong Gateway for routing, rate limiting, and monitoring in Zpanel.</div>

    @if (session('success'))
        <x-toast type="success" message="{{ session('success') }}" />
    @endif

    @if ($errors->any())
        <x-toast type="error" message="{{ $errors->first() }}" />
    @endif

    <div class="grid gap-4 lg:grid-cols-2 mt-4">
        @forelse ($services as $service)
            <div class="box group">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col flex-1">
                        <div class="box-title">{{ $service->name }}</div>
                        <div class="box-description">
                            URL: <code class="text-xs">{{ $service->url }}</code>
                        </div>
                        <div class="box-description flex items-center gap-2">
                            Status: 
                            @if ($service->status === 'active')
                                <span class="text-success">● Active</span>
                            @else
                                <span class="text-warning">● Inactive</span>
                            @endif
                        </div>
                        @if ($service->routes && count($service->routes) > 0)
                            <div class="box-description text-xs">
                                Routes: {{ count($service->routes) }} configured
                            </div>
                        @endif
                        @if ($service->plugins && count($service->plugins) > 0)
                            <div class="box-description text-xs">
                                Plugins: {{ count($service->plugins) }} active
                            </div>
                        @endif
                        <div class="box-description text-xs text-neutral-400">
                            Kong Service ID: {{ substr($service->kong_service_id, 0, 8) }}...
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('api-gateway.edit', $service->id) }}" class="text-sm hover:underline">Edit</a>
                        <form action="{{ route('api-gateway.destroy', $service->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this API service? This will also remove it from Kong.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-error hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2">
                <div class="box">
                    <div class="flex flex-col items-center justify-center py-8">
                        <p class="mb-4">No API services found.</p>
                        <a href="{{ route('api-gateway.create') }}">
                            <x-forms.button>+ Create Your First Service</x-forms.button>
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($services->count() > 0)
        <div class="mt-6 pt-4 border-t border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-semibold mb-2">Kong Gateway Status</h3>
            <div id="kong-health-status" class="text-sm">
                <span class="text-neutral-400">Checking Kong health...</span>
            </div>
        </div>
    @endif
</x-layout>

<script>
    // Check Kong health on page load
    fetch('{{ route("api-gateway.health") }}')
        .then(response => response.json())
        .then(data => {
            const statusEl = document.getElementById('kong-health-status');
            if (data.status === 'healthy') {
                statusEl.innerHTML = '<span class="text-success">● Kong Gateway: Healthy</span>';
            } else if (data.status === 'unhealthy') {
                statusEl.innerHTML = '<span class="text-warning">⚠ Kong Gateway: Unhealthy</span>';
            } else {
                statusEl.innerHTML = '<span class="text-error">✕ Kong Gateway: Unreachable</span>';
            }
        })
        .catch(error => {
            const statusEl = document.getElementById('kong-health-status');
            statusEl.innerHTML = '<span class="text-error">✕ Kong Gateway: Connection Error</span>';
        });
</script>

