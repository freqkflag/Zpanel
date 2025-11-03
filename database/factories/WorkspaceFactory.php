<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'path' => '/workspace/user_'.fake()->numberBetween(1, 1000),
            'project_id' => null,
            'settings' => [
                'theme' => 'dark',
                'fontSize' => 14,
            ],
            'last_accessed_at' => now(),
        ];
    }

    public function withProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => 'project-'.fake()->uuid(),
            'path' => $attributes['path'].'/project_'.fake()->uuid(),
        ]);
    }
}
