<?php

use App\Models\APIService;
use App\Models\User;
use App\Services\APIGateway\KongService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('api gateway index page loads successfully', function () {
    $response = $this->get(route('api-gateway.index'));

    $response->assertSuccessful();
    $response->assertSee('API Gateway');
    $response->assertSee('Zpanel');
});

test('api gateway create page loads successfully', function () {
    $response = $this->get(route('api-gateway.create'));

    $response->assertSuccessful();
    $response->assertSee('Create API Service');
    $response->assertSee('Service Name');
    $response->assertSee('Backend URL');
});

test('can create api service with valid data', function () {
    // Mock Kong service
    $this->mock(KongService::class, function ($mock) {
        $mock->shouldReceive('createService')
            ->once()
            ->andReturn(['id' => 'kong-service-123', 'name' => 'test-api']);

        $mock->shouldReceive('createRoute')
            ->once()
            ->andReturn(['id' => 'route-123', 'paths' => ['/api/test']]);

        $mock->shouldReceive('manageRateLimiting')
            ->once()
            ->andReturn(['id' => 'plugin-123', 'name' => 'rate-limiting']);
    });

    $response = $this->post(route('api-gateway.store'), [
        'name' => 'test-api-service',
        'url' => 'http://backend:3000',
        'paths' => ['/api/test'],
        'rate_limit' => 100,
    ]);

    $response->assertRedirect(route('api-gateway.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('a_p_i_services', [
        'name' => 'test-api-service',
        'url' => 'http://backend:3000',
        'status' => 'active',
    ]);
});

test('api service creation validates required fields', function () {
    $response = $this->post(route('api-gateway.store'), []);

    $response->assertSessionHasErrors(['name', 'url', 'paths']);
});

test('can update existing api service', function () {
    $service = APIService::factory()->create([
        'kong_service_id' => 'kong-123',
        'name' => 'original-name',
        'url' => 'http://original:3000',
    ]);

    $this->mock(KongService::class, function ($mock) {
        $mock->shouldReceive('updateService')
            ->once()
            ->andReturn(['id' => 'kong-123']);
    });

    $response = $this->patch(route('api-gateway.update', $service), [
        'name' => 'updated-name',
        'url' => 'http://updated:3000',
        'status' => 'inactive',
    ]);

    $response->assertRedirect(route('api-gateway.index'));

    $service->refresh();
    expect($service->name)->toBe('updated-name');
    expect($service->url)->toBe('http://updated:3000');
    expect($service->status)->toBe('inactive');
});

test('can delete api service', function () {
    $service = APIService::factory()->create([
        'kong_service_id' => 'kong-123',
    ]);

    $this->mock(KongService::class, function ($mock) {
        $mock->shouldReceive('deleteService')
            ->once()
            ->andReturn(true);
    });

    $response = $this->delete(route('api-gateway.destroy', $service));

    $response->assertRedirect(route('api-gateway.index'));
    $this->assertDatabaseMissing('a_p_i_services', ['id' => $service->id]);
});

test('api gateway health endpoint returns json', function () {
    $this->mock(KongService::class, function ($mock) {
        $mock->shouldReceive('healthCheck')
            ->once()
            ->andReturn(['status' => 'healthy']);
    });

    $response = $this->get(route('api-gateway.health'));

    $response->assertSuccessful();
    $response->assertJson(['status' => 'healthy']);
});

test('edit page displays existing service data', function () {
    $service = APIService::factory()->create([
        'name' => 'my-api',
        'url' => 'http://backend:3000',
        'status' => 'active',
    ]);

    $response = $this->get(route('api-gateway.edit', $service));

    $response->assertSuccessful();
    $response->assertSee('my-api');
    $response->assertSee('http://backend:3000');
});
