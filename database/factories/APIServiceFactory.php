<?php

namespace Database\Factories;

use App\Models\APIService;
use Illuminate\Database\Eloquent\Factories\Factory;

class APIServiceFactory extends Factory
{
    protected $model = APIService::class;

    public function definition(): array
    {
        return [
            'kong_service_id' => 'kong-'.fake()->uuid(),
            'name' => fake()->slug(2),
            'url' => 'http://'.fake()->domainName().':'.fake()->numberBetween(3000, 9000),
            'routes' => [
                [
                    'id' => 'route-'.fake()->uuid(),
                    'paths' => ['/api/'.fake()->word()],
                ],
            ],
            'plugins' => [],
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withRateLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'plugins' => [
                [
                    'id' => 'plugin-'.fake()->uuid(),
                    'name' => 'rate-limiting',
                    'config' => ['minute' => 60],
                ],
            ],
        ]);
    }
}
