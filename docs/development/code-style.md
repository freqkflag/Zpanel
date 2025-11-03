# Code Style Guide

Zpanel follows strict code quality and style standards to ensure maintainability and consistency.

## PHP Code Style

### Standards

We follow **PSR-12** coding standards with Laravel-specific enhancements.

### Formatting Tool

**Laravel Pint** is used for automatic code formatting.

```bash
# Format all PHP files
./vendor/bin/pint

# Check formatting without making changes
./vendor/bin/pint --test

# Format only changed files (Git)
./vendor/bin/pint --dirty
```

### Configuration

Pint configuration in `pint.json`:

```json
{
    "preset": "laravel",
    "rules": {
        "array_syntax": {"syntax": "short"},
        "binary_operator_spaces": true,
        "blank_line_after_opening_tag": true,
        "blank_line_before_statement": true,
        "cast_spaces": {"space": "single"},
        "concat_space": {"spacing": "one"}
    }
}
```

### PHP Conventions

#### Type Declarations

Always use explicit type declarations:

```php
// ✅ GOOD
public function deploy(Application $application): ApplicationDeploymentQueue
{
    return $this->service->deploy($application);
}

// ❌ BAD
public function deploy($application)
{
    return $this->service->deploy($application);
}
```

#### Constructor Property Promotion

Use PHP 8.4 constructor property promotion:

```php
// ✅ GOOD
public function __construct(
    private readonly DockerService $dockerService,
    private readonly ConfigGenerator $configGenerator
) {}

// ❌ BAD
private $dockerService;
private $configGenerator;

public function __construct(DockerService $dockerService, ConfigGenerator $configGenerator)
{
    $this->dockerService = $dockerService;
    $this->configGenerator = $configGenerator;
}
```

#### Enums

Use TitleCase for enum values:

```php
enum DeploymentStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Success = 'success';
    case Failed = 'failed';
}
```

#### Method Naming

Use descriptive, verb-based names:

```php
// ✅ GOOD
public function isDeploymentAllowed(): bool
public function canAccessResource(User $user): bool
public function fetchRemoteConfiguration(): array

// ❌ BAD
public function deployment(): bool
public function access(): bool
public function remote(): array
```

## Laravel Conventions

### Models

```php
class Application extends Model
{
    // Use casts() method (Laravel 12)
    protected function casts(): array
    {
        return [
            'environment_variables' => 'array',
            'settings' => 'array',
            'deployed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
    
    // Explicit return types for relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
    
    public function deployments(): HasMany
    {
        return $this->hasMany(ApplicationDeploymentQueue::class);
    }
}
```

### Controllers

```php
class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService
    ) {
        $this->middleware('auth:sanctum');
    }
    
    public function index(Request $request): JsonResponse
    {
        $applications = $this->applicationService->list(
            $request->user()->currentTeam
        );
        
        return response()->json(['data' => $applications]);
    }
}
```

### Form Requests

```php
class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Application::class);
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'git_repository' => 'required|url',
            'server_id' => 'required|exists:servers,id',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Application name is required',
            'git_repository.url' => 'Please provide a valid Git repository URL',
        ];
    }
}
```

## JavaScript/TypeScript Style

### Formatting

Use Prettier for JavaScript formatting (when configured):

```bash
npm run format
```

### Alpine.js Conventions

```javascript
// ✅ GOOD - Clear component structure
Alpine.data('deploymentMonitor', () => ({
    status: 'idle',
    logs: [],
    
    init() {
        this.connectWebSocket();
    },
    
    connectWebSocket() {
        Echo.private(`application.${this.applicationId}`)
            .listen('DeploymentStarted', (e) => {
                this.status = 'deploying';
            });
    }
}));

// ❌ BAD - Unclear structure
Alpine.data('monitor', () => ({
    s: 'idle',
    l: [],
    init() { /* ... */ }
}));
```

## CSS/Tailwind Conventions

### Utility Classes

Use Tailwind utilities, following the project's patterns:

```html
<!-- ✅ GOOD - Consistent spacing with gap -->
<div class="flex gap-4">
    <div class="px-4 py-2">Item 1</div>
    <div class="px-4 py-2">Item 2</div>
</div>

<!-- ❌ BAD - Inconsistent margins -->
<div class="flex">
    <div class="mr-4 px-4 py-2">Item 1</div>
    <div class="px-4 py-2">Item 2</div>
</div>
```

### Dark Mode

Always include dark mode styles:

```html
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    Content
</div>
```

### Component Classes

Extract repeated patterns:

```html
<!-- ✅ GOOD - Reusable component -->
<x-card class="p-6">
    <h3 class="text-lg font-semibold mb-4">Title</h3>
    <p>Content</p>
</x-card>

<!-- ❌ BAD - Repeated styles -->
<div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Title</h3>
</div>
```

## Documentation Style

### PHP DocBlocks

```php
/**
 * Deploy an application to the specified server.
 * 
 * This method creates a deployment queue entry and dispatches
 * a background job to handle the actual build and deployment.
 * 
 * @param Application $application The application to deploy
 * @param array<string, mixed> $options Deployment options
 * @return ApplicationDeploymentQueue The created deployment queue
 * 
 * @throws DeploymentException When deployment cannot be started
 * @throws ServerConnectionException When server is unreachable
 */
public function deploy(Application $application, array $options = []): ApplicationDeploymentQueue
{
    // Implementation
}
```

### Markdown

- Use ATX-style headers (`#` instead of underlines)
- Use fenced code blocks with language specifiers
- Keep line length reasonable (80-120 characters)
- Use proper list formatting

## Git Commit Messages

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Test additions or changes
- `chore`: Build process or auxiliary tool changes
- `ci`: CI/CD changes

### Examples

```
feat(ide): add workspace management functionality

- Implement workspace creation and deletion
- Add workspace isolation per user
- Add workspace settings persistence

Closes #123
```

```
fix(deployment): resolve container health check timeout

Container health checks were timing out due to incorrect
interval configuration. Updated default interval from 10s to 30s.

Fixes #456
```

## File Organization

### Directory Structure

```
app/
├── Actions/           # Business logic actions
├── Http/
│   ├── Controllers/  # HTTP controllers
│   ├── Middleware/   # Middleware classes
│   └── Requests/     # Form request validation
├── Jobs/             # Queue jobs
├── Livewire/         # Livewire components
├── Models/           # Eloquent models
├── Services/         # Service classes
└── Rules/            # Validation rules
```

### Naming Conventions

- **Controllers**: Singular noun + Controller (e.g., `ApplicationController`)
- **Models**: Singular noun (e.g., `Application`, `Server`)
- **Services**: Noun + Service (e.g., `DeploymentService`)
- **Jobs**: Verb + Noun + Job (e.g., `DeployApplicationJob`)
- **Actions**: Verb + Noun (e.g., `DeployApplication`)

## Code Review Checklist

Before submitting PR:

- [ ] Run `./vendor/bin/pint`
- [ ] Run `./vendor/bin/phpstan analyse`
- [ ] Run tests (`./vendor/bin/pest`)
- [ ] Update documentation
- [ ] Add/update tests for changes
- [ ] Check no debug code remains
- [ ] Verify proper error handling
- [ ] Ensure backward compatibility

## Tools Integration

### IDE Configuration

**VS Code** (`settings.json`):
```json
{
  "[php]": {
    "editor.defaultFormatter": "open-southeners.laravel-pint",
    "editor.formatOnSave": true
  },
  "intelephense.telemetry.enabled": false,
  "php.suggest.basic": false
}
```

**PHPStorm**: Use Laravel plugin and PHP code style based on PSR-12.

## Additional Resources

- [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Laravel Code Style](https://laravel.com/docs/contributions#coding-style)
- [Tailwind CSS Best Practices](https://tailwindcss.com/docs/reusing-styles)
- [Conventional Commits](https://www.conventionalcommits.org/)

