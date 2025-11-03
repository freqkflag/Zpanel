<?php

namespace Database\Factories;

use App\Models\MCPServer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MCPServerFactory extends Factory
{
    protected $model = MCPServer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->slug(2).'-mcp',
            'type' => fake()->randomElement(['cloudflare', 'github', 'database', 'docker', 'custom']),
            'config' => [
                'command' => fake()->randomElement(['php', 'node', 'python']),
                'args' => ['artisan', fake()->word()],
            ],
            'status' => 'active',
            'last_health_check' => null,
            'last_error' => null,
        ];
    }

    public function cloudflare(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MCPServer::TYPE_CLOUDFLARE,
            'config' => [
                'command' => 'php',
                'args' => ['artisan', 'mcp:cloudflare'],
                'api_token' => fake()->sha256(),
            ],
        ]);
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MCPServer::TYPE_GITHUB,
            'config' => [
                'command' => 'node',
                'args' => ['mcp-github'],
                'access_token' => fake()->sha256(),
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'last_error' => 'Connection failed: '.fake()->sentence(),
        ]);
    }
}
