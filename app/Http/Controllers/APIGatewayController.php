<?php

namespace App\Http\Controllers;

use App\Models\APIService;
use App\Services\APIGateway\KongService;
use Illuminate\Http\Request;

class APIGatewayController extends Controller
{
    public function __construct(
        private KongService $kongService
    ) {
        $this->middleware('auth');
    }

    /**
     * List all API services
     */
    public function index()
    {
        $services = APIService::all();

        return view('api-gateway.index', [
            'services' => $services,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('api-gateway.create');
    }

    /**
     * Store new API service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:a_p_i_services,name',
            'url' => 'required|url',
            'paths' => 'required|array',
            'paths.*' => 'required|string',
            'rate_limit' => 'nullable|integer|min:1',
        ]);

        try {
            // Create service in Kong
            $kongService = $this->kongService->createService(
                $validated['name'],
                $validated['url']
            );

            // Create route in Kong
            $route = $this->kongService->createRoute(
                $kongService['id'],
                $validated['paths']
            );

            // Add rate limiting if specified
            $plugins = [];
            if (isset($validated['rate_limit'])) {
                $rateLimitPlugin = $this->kongService->manageRateLimiting(
                    $kongService['id'],
                    $validated['rate_limit']
                );
                $plugins[] = $rateLimitPlugin;
            }

            // Save to database
            $service = APIService::create([
                'kong_service_id' => $kongService['id'],
                'name' => $validated['name'],
                'url' => $validated['url'],
                'routes' => [$route],
                'plugins' => $plugins,
                'status' => 'active',
            ]);

            return redirect()->route('api-gateway.index')
                ->with('success', 'API Service created successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create service: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show edit form
     */
    public function edit(APIService $apiService)
    {
        return view('api-gateway.edit', [
            'service' => $apiService,
        ]);
    }

    /**
     * Update API service
     */
    public function update(Request $request, APIService $apiService)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:a_p_i_services,name,'.$apiService->id,
            'url' => 'required|url',
            'status' => 'required|string|in:active,inactive',
        ]);

        try {
            // Update service in Kong
            $this->kongService->updateService($apiService->kong_service_id, [
                'name' => $validated['name'],
                'url' => $validated['url'],
            ]);

            // Update in database
            $apiService->update($validated);

            return redirect()->route('api-gateway.index')
                ->with('success', 'API Service updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update service: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete API service
     */
    public function destroy(APIService $apiService)
    {
        try {
            // Delete from Kong
            $this->kongService->deleteService($apiService->kong_service_id);

            // Delete from database
            $apiService->delete();

            return redirect()->route('api-gateway.index')
                ->with('success', 'API Service deleted successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete service: '.$e->getMessage()]);
        }
    }

    /**
     * Show analytics for a service
     */
    public function analytics(APIService $apiService)
    {
        return view('api-gateway.analytics', [
            'service' => $apiService,
        ]);
    }

    /**
     * Check Kong health
     */
    public function health()
    {
        $health = $this->kongService->healthCheck();

        return response()->json($health);
    }
}
