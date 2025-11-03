<x-layout>
    <x-slot:title>
        Create API Service | Zpanel
    </x-slot>
    <h1>Create API Service</h1>
    <div class="subtitle">Configure a new API service in Kong Gateway with routing and rate limiting.</div>

    @if ($errors->any())
        <div class="box bg-error/10 border-error text-error mt-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('api-gateway.store') }}" method="POST" class="max-w-3xl mt-4">
        @csrf

        <div class="box">
            <h2 class="text-lg font-semibold mb-4">Service Details</h2>

            <x-forms.input 
                id="name" 
                name="name"
                label="Service Name" 
                required 
                helper="Unique identifier for this API service (e.g., user-api, payment-service)"
                :value="old('name')" />

            <x-forms.input 
                id="url" 
                name="url"
                label="Backend URL" 
                type="url"
                required 
                helper="Full URL of your backend service (e.g., http://backend:3000)"
                placeholder="http://your-service:port"
                :value="old('url')" />
        </div>

        <div class="box mt-4">
            <h2 class="text-lg font-semibold mb-4">Routing Configuration</h2>

            <div class="mb-4">
                <label for="paths" class="flex gap-1 items-center mb-1 text-sm font-medium">
                    Route Paths
                    <x-highlighted text="*" />
                </label>
                <div id="paths-container" class="space-y-2">
                    <div class="flex gap-2 items-center path-input-group">
                        <input 
                            type="text" 
                            name="paths[]" 
                            class="input flex-1" 
                            placeholder="/api/users"
                            value="{{ old('paths.0', '/api') }}"
                            required />
                        <button type="button" onclick="removePathInput(this)" class="text-error hover:underline text-sm hidden">Remove</button>
                    </div>
                </div>
                <button type="button" onclick="addPathInput()" class="text-sm text-primary hover:underline mt-2">
                    + Add Another Path
                </button>
                <small class="text-xs text-neutral-400 block mt-1">
                    Define URL paths to route to this service (e.g., /api/v1, /users)
                </small>
            </div>
        </div>

        <div class="box mt-4">
            <h2 class="text-lg font-semibold mb-4">Rate Limiting (Optional)</h2>

            <x-forms.input 
                id="rate_limit" 
                name="rate_limit"
                label="Requests Per Minute" 
                type="number"
                min="1"
                helper="Maximum number of requests allowed per minute (leave empty for unlimited)"
                placeholder="60"
                :value="old('rate_limit')" />
        </div>

        <div class="flex gap-2 mt-6">
            <x-forms.button type="submit">Create Service</x-forms.button>
            <a href="{{ route('api-gateway.index') }}">
                <x-forms.button type="button" class="bg-neutral-500">Cancel</x-forms.button>
            </a>
        </div>
    </form>
</x-layout>

<script>
    function addPathInput() {
        const container = document.getElementById('paths-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center path-input-group';
        div.innerHTML = `
            <input 
                type="text" 
                name="paths[]" 
                class="input flex-1" 
                placeholder="/api/v2"
                required />
            <button type="button" onclick="removePathInput(this)" class="text-error hover:underline text-sm">Remove</button>
        `;
        container.appendChild(div);
        updateRemoveButtons();
    }

    function removePathInput(button) {
        button.closest('.path-input-group').remove();
        updateRemoveButtons();
    }

    function updateRemoveButtons() {
        const groups = document.querySelectorAll('.path-input-group');
        groups.forEach((group, index) => {
            const removeBtn = group.querySelector('button');
            if (groups.length === 1) {
                removeBtn.classList.add('hidden');
            } else {
                removeBtn.classList.remove('hidden');
            }
        });
    }

    // Initialize on page load
    updateRemoveButtons();
</script>

