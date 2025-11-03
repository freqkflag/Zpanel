# Testing Strategy

Comprehensive testing strategy for Zpanel to ensure reliability, security, and performance.

## Testing Philosophy

- **Test early, test often**
- **Prefer mocking over database** in unit tests
- **Feature tests for user workflows**
- **E2E tests for critical paths**
- **Performance tests for scalability**

## Test Pyramid

```
         /\
        /  \    E2E Tests (Browser/Dusk)
       /    \   - Critical user workflows
      /------\  - Smoke tests
     /        \ 
    /          \ Feature Tests (Integration)
   /            \ - API endpoints
  /--------------\ - Database interactions
 /                \
/------------------\ Unit Tests
- Business logic     - Isolated components
- Helper functions   - Service classes
- Validators         - Mocked dependencies
```

## Test Categories

### 1. Unit Tests (`tests/Unit/`)

**Purpose**: Test isolated components without external dependencies

**Characteristics:**
- No database connections
- Use mocking for dependencies
- Fast execution (<1s per test)
- Run outside Docker

**Example:**
```php
// tests/Unit/Services/ConfigurationGeneratorTest.php
test('generates docker compose configuration', function () {
    $application = Mockery::mock(Application::class);
    $application->shouldReceive('name')->andReturn('test-app');
    $application->shouldReceive('getEnvironmentVariables')->andReturn([]);
    
    $generator = new ConfigurationGenerator();
    $config = $generator->generateDockerCompose($application);
    
    expect($config)->toBeArray();
    expect($config)->toHaveKey('services');
});
```

**Coverage Target**: ≥80%

### 2. Feature Tests (`tests/Feature/`)

**Purpose**: Test application features and integrations

**Characteristics:**
- May use database (with RefreshDatabase)
- Test HTTP endpoints
- Test user workflows
- Run inside Docker container

**Example:**
```php
// tests/Feature/ApplicationDeploymentTest.php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can deploy application via API', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create([
        'team_id' => $user->currentTeam->id
    ]);
    
    $response = $this->actingAs($user)
        ->postJson("/api/v1/applications/{$application->id}/deploy");
    
    $response->assertOk();
    expect($application->fresh()->deployments)->toHaveCount(1);
});
```

**Coverage Target**: ≥70%

### 3. Browser Tests (`tests/Browser/`)

**Purpose**: Test UI interactions and user experience

**Characteristics:**
- Uses Laravel Dusk
- Real browser automation
- End-to-end testing
- Slower execution

**Example:**
```php
// tests/Browser/ApplicationManagementTest.php
test('user can create application through UI', function () {
    $user = User::factory()->create();
    
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/applications/create')
            ->type('name', 'Test Application')
            ->select('server_id', 1)
            ->press('Create Application')
            ->assertSee('Application created successfully')
            ->assertPathIs('/applications/*');
    });
});
```

**Coverage Target**: Critical user flows only

### 4. Integration Tests

**Purpose**: Test interactions between components

**Location**: `tests/Feature/Integration/`

**Example:**
```php
// tests/Feature/Integration/CloudflareIntegrationTest.php
test('cloudflare DNS updates on domain change', function () {
    $application = Application::factory()->create([
        'fqdn' => 'app.example.com'
    ]);
    
    // Mock Cloudflare API
    Http::fake([
        'api.cloudflare.com/*' => Http::response(['success' => true])
    ]);
    
    $application->update(['fqdn' => 'new.example.com']);
    
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.cloudflare.com/client/v4/zones/*/dns_records';
    });
});
```

## Testing Tools

### Pest PHP

Primary testing framework with expressive syntax.

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ApplicationTest.php

# Run tests matching pattern
php artisan test --filter=deployment

# Run with coverage
php artisan test --coverage --min=70
```

### Laravel Dusk

Browser automation testing.

```bash
# Run Dusk tests
php artisan dusk

# Run specific Dusk test
php artisan dusk tests/Browser/LoginTest.php
```

### Mockery

Mocking framework for dependencies.

```php
use Mockery;

$mock = Mockery::mock(SomeClass::class);
$mock->shouldReceive('method')->andReturn('value');
$mock->shouldReceive('anotherMethod')->once()->with('arg');
```

## Testing Best Practices

### 1. Arrange-Act-Assert Pattern

```php
test('example test', function () {
    // Arrange - Set up test data
    $user = User::factory()->create();
    $application = Application::factory()->create();
    
    // Act - Perform action
    $result = $application->deploy();
    
    // Assert - Verify outcome
    expect($result)->toBeInstanceOf(ApplicationDeploymentQueue::class);
    expect($result->status)->toBe('queued');
});
```

### 2. Test Naming

Use descriptive test names:

```php
// ✅ GOOD
test('user can deploy application when they have deploy permission', function () {});
test('deployment fails when server is unreachable', function () {});

// ❌ BAD
test('deploy', function () {});
test('test1', function () {});
```

### 3. Database Testing

Use factories and seeders:

```php
// ✅ GOOD - Use factories
$user = User::factory()->admin()->create();
$application = Application::factory()->for($user->currentTeam)->create();

// ❌ BAD - Manual creation
$user = new User();
$user->email = 'test@example.com';
$user->save();
```

### 4. Mocking External Services

Always mock external APIs and services:

```php
// ✅ GOOD - Mock external service
Http::fake([
    'api.cloudflare.com/*' => Http::response(['success' => true])
]);

$service->syncDNS();

Http::assertSent(function ($request) {
    return str_contains($request->url(), 'cloudflare.com');
});

// ❌ BAD - Real API calls in tests
$service->syncDNS(); // Makes real API call!
```

### 5. Test Data Cleanup

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class); // Automatic cleanup after each test
```

## Performance Testing

### Load Testing

Use k6 for load testing:

```javascript
// tests/Performance/deployment-load-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
    vus: 10,
    duration: '30s',
};

export default function () {
    let response = http.post(
        'https://your-domain.com/api/v1/applications/1/deploy',
        null,
        {
            headers: {
                'Authorization': 'Bearer ' + __ENV.API_TOKEN
            }
        }
    );
    
    check(response, {
        'status is 200': (r) => r.status === 200,
        'deployment started': (r) => r.json('deployment_id') !== undefined
    });
    
    sleep(1);
}
```

Run:
```bash
k6 run tests/Performance/deployment-load-test.js
```

## Test Coverage

### Coverage Goals

- **Unit Tests**: ≥80% coverage
- **Feature Tests**: ≥70% coverage
- **Overall**: ≥75% coverage

### Generating Coverage Reports

```bash
# HTML coverage report
php artisan test --coverage-html coverage/

# View report
open coverage/index.html

# Coverage summary
php artisan test --coverage --min=75
```

## Continuous Integration

### GitHub Actions

Tests run automatically on:
- Every push to `main`, `next`, `develop`
- Every pull request
- Nightly builds

### CI Workflow

1. **Code quality** checks (Pint, PHPStan)
2. **Unit tests** (no database)
3. **Feature tests** (with database)
4. **Security scanning**
5. **Build Docker image** (on success)

## Test Data Management

### Factories

Create comprehensive factories:

```php
class ApplicationFactory extends Factory
{
    protected $model = Application::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->slug(2),
            'fqdn' => $this->faker->domainName,
            'git_repository' => 'https://github.com/user/repo.git',
            'git_branch' => 'main',
        ];
    }
    
    public function deployed(): static
    {
        return $this->state(fn () => [
            'status' => 'running',
            'deployed_at' => now(),
        ]);
    }
    
    public function withServer(): static
    {
        return $this->state(fn () => [
            'server_id' => Server::factory(),
        ]);
    }
}
```

### Seeders

```php
class TestSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['current_team_id' => $team->id]);
        
        Application::factory()
            ->count(5)
            ->for($team)
            ->create();
    }
}
```

## Testing Checklist

Before every commit:

- [ ] All tests passing locally
- [ ] New tests added for new features
- [ ] Updated tests for modified features
- [ ] No commented-out tests
- [ ] Proper mocking used
- [ ] Test data uses factories
- [ ] Coverage meets minimum threshold

## Additional Resources

- [Pest PHP Documentation](https://pestphp.com/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [Laravel Dusk Documentation](https://laravel.com/docs/dusk)
- [Testing Patterns (Cursor Rules)](../../implementation/phase-1/Zpanel/.cursor/rules/testing-patterns.mdc)

