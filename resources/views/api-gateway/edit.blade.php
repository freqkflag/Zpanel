<x-layout>
    <x-slot:title>
        Edit API Service | Zpanel
    </x-slot>
    <h1>Edit API Service</h1>
    <div class="subtitle">Update configuration for {{ $service->name }}</div>

    @if ($errors->any())
        <div class="box bg-error/10 border-error text-error mt-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('api-gateway.update', $service->id) }}" method="POST" class="max-w-3xl mt-4">
        @csrf
        @method('PATCH')

        <div class="box">
            <h2 class="text-lg font-semibold mb-4">Service Details</h2>

            <x-forms.input 
                id="name" 
                name="name"
                label="Service Name" 
                required 
                helper="Unique identifier for this API service"
                :value="old('name', $service->name)" />

            <x-forms.input 
                id="url" 
                name="url"
                label="Backend URL" 
                type="url"
                required 
                helper="Full URL of your backend service"
                :value="old('url', $service->url)" />

            <div class="mt-4">
                <label for="status" class="flex gap-1 items-center mb-1 text-sm font-medium">
                    Status
                    <x-highlighted text="*" />
                </label>
                <select name="status" id="status" class="input" required>
                    <option value="active" {{ old('status', $service->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $service->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <small class="text-xs text-neutral-400 block mt-1">
                    Set to inactive to temporarily disable this service
                </small>
            </div>
        </div>

        <div class="box mt-4">
            <h2 class="text-lg font-semibold mb-4">Service Information</h2>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-neutral-400">Kong Service ID:</span>
                    <code class="text-xs">{{ $service->kong_service_id }}</code>
                </div>

                @if ($service->routes && count($service->routes) > 0)
                    <div>
                        <span class="text-neutral-400">Configured Routes:</span>
                        <ul class="list-disc list-inside ml-4 mt-1">
                            @foreach ($service->routes as $route)
                                @if (isset($route['paths']))
                                    @foreach ($route['paths'] as $path)
                                        <li><code class="text-xs">{{ $path }}</code></li>
                                    @endforeach
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($service->plugins && count($service->plugins) > 0)
                    <div>
                        <span class="text-neutral-400">Active Plugins:</span>
                        <ul class="list-disc list-inside ml-4 mt-1">
                            @foreach ($service->plugins as $plugin)
                                @if (isset($plugin['name']))
                                    <li>{{ ucfirst($plugin['name']) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-between text-xs text-neutral-500">
                    <span>Created: {{ $service->created_at->format('Y-m-d H:i:s') }}</span>
                    <span>Updated: {{ $service->updated_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <x-forms.button type="submit">Update Service</x-forms.button>
            <a href="{{ route('api-gateway.index') }}">
                <x-forms.button type="button" class="bg-neutral-500">Cancel</x-forms.button>
            </a>
        </div>
    </form>

    <div class="box bg-error/10 border-error mt-8 max-w-3xl">
        <h3 class="text-lg font-semibold text-error mb-2">Danger Zone</h3>
        <p class="text-sm mb-4">Deleting this service will remove it from Kong Gateway and cannot be undone.</p>
        <form action="{{ route('api-gateway.destroy', $service->id) }}" method="POST" 
            onsubmit="return confirm('Are you sure you want to delete this API service? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <x-forms.button type="submit" class="bg-error">Delete Service</x-forms.button>
        </form>
    </div>
</x-layout>

