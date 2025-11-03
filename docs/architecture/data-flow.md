# Data Flow Architecture

Comprehensive documentation of how data flows through Zpanel system.

## User Authentication Flow

```
┌──────────┐
│  Browser │
└─────┬────┘
      │ 1. POST /login
      ▼
┌──────────────────┐
│ Laravel Router   │
└─────┬────────────┘
      │ 2. Route to LoginController
      ▼
┌──────────────────┐
│ Fortify          │
└─────┬────────────┘
      │ 3. Validate credentials
      ▼
┌──────────────────┐
│ User Model       │
└─────┬────────────┘
      │ 4. Check password hash
      ▼
┌──────────────────┐
│ Session Store    │ 5. Create session
│ (Redis)          │
└─────┬────────────┘
      │ 6. Set session cookie
      ▼
┌──────────┐
│  Browser │ 7. Redirect to dashboard
└──────────┘
```

## Application Deployment Data Flow

```
┌────────────────┐
│ Git Repository │
└───────┬────────┘
        │ 1. git push
        ▼
┌────────────────────┐
│ GitHub/GitLab/etc  │
└───────┬────────────┘
        │ 2. Webhook POST
        ▼
┌────────────────────────┐
│ Webhook Controller     │
│ routes/webhooks.php    │
└───────┬────────────────┘
        │ 3. Validate & parse
        ▼
┌────────────────────────┐
│ Application Model      │
│ - Find by repository   │
│ - Check branch matches │
└───────┬────────────────┘
        │ 4. Create deployment record
        ▼
┌────────────────────────────────┐
│ ApplicationDeploymentQueue     │
│ - status: queued               │
│ - commit_sha: abc123           │
└───────┬────────────────────────┘
        │ 5. Dispatch job
        ▼
┌────────────────────────────────┐
│ Redis Queue                    │
│ - Store serialized job         │
└───────┬────────────────────────┘
        │ 6. Horizon worker picks up
        ▼
┌────────────────────────────────┐
│ DeployApplicationJob           │
│ - Update status: running       │
│ - Clone/pull repository        │
│ - Build Docker image           │
│ - Deploy container             │
│ - Run health checks            │
│ - Update proxy config          │
│ - Update status: success/fail  │
└───────┬────────────────────────┘
        │ 7. Broadcast events
        ▼
┌────────────────────────────────┐
│ Soketi (WebSocket)             │
│ - Emit deployment.started      │
│ - Emit deployment.log          │
│ - Emit deployment.completed    │
└───────┬────────────────────────┘
        │ 8. Frontend receives
        ▼
┌────────────────────────────────┐
│ Livewire Component             │
│ - Update deployment status     │
│ - Display logs in real-time    │
│ - Show success/error message   │
└────────────────────────────────┘
```

## API Request Data Flow

```
┌──────────────┐
│ API Client   │
└──────┬───────┘
       │ 1. POST /api/v1/applications/{id}/deploy
       │    Authorization: Bearer TOKEN
       ▼
┌────────────────────┐
│ routes/api.php     │
└──────┬─────────────┘
       │ 2. Apply middleware
       ▼
┌────────────────────┐
│ Sanctum Auth       │
│ - Validate token   │
│ - Load user        │
└──────┬─────────────┘
       │ 3. Authorized user
       ▼
┌────────────────────┐
│ Rate Limiter       │
│ - Check limits     │
└──────┬─────────────┘
       │ 4. Under limit
       ▼
┌────────────────────┐
│ Team Middleware    │
│ - Verify team      │
│ - Set context      │
└──────┬─────────────┘
       │ 5. Team verified
       ▼
┌─────────────────────────┐
│ ApplicationController   │
│ - authorize('deploy')   │
└──────┬──────────────────┘
       │ 6. Authorized
       ▼
┌─────────────────────────┐
│ DeploymentService       │
│ - Create deployment     │
│ - Queue job             │
└──────┬──────────────────┘
       │ 7. Return response
       ▼
┌─────────────────────────┐
│ API Resource            │
│ - Format response       │
└──────┬──────────────────┘
       │ 8. JSON response
       ▼
┌──────────────┐
│ API Client   │
└──────────────┘
```

## Database Query Flow

### Eloquent Query Flow

```
┌─────────────────┐
│ Controller      │
└────────┬────────┘
         │ Application::with('server')->get()
         ▼
┌─────────────────┐
│ Eloquent ORM    │
└────────┬────────┘
         │ Build SQL query
         ▼
┌─────────────────┐
│ Query Builder   │
└────────┬────────┘
         │ SELECT * FROM applications
         │ SELECT * FROM servers WHERE id IN (...)
         ▼
┌─────────────────┐
│ PDO Driver      │
└────────┬────────┘
         │ Execute query
         ▼
┌─────────────────┐
│ PostgreSQL      │
└────────┬────────┘
         │ Return results
         ▼
┌─────────────────┐
│ Eloquent Models │
│ - Hydrate       │
│ - Relations     │
│ - Casts         │
└────────┬────────┘
         │ Return collection
         ▼
┌─────────────────┐
│ Controller      │
└─────────────────┘
```

### Query Optimization

**N+1 Problem:**
```php
// ❌ BAD - N+1 queries
$applications = Application::all(); // 1 query
foreach ($applications as $app) {
    echo $app->server->name; // N queries!
}

// ✅ GOOD - Eager loading
$applications = Application::with('server')->get(); // 2 queries total
foreach ($applications as $app) {
    echo $app->server->name; // No additional query
}
```

## Real-Time Data Flow (WebSocket)

```
┌──────────────────┐
│ Backend Event    │
│ (Deployment log) │
└────────┬─────────┘
         │ broadcast(new DeploymentLog($data))
         ▼
┌──────────────────┐
│ Laravel Echo     │
└────────┬─────────┘
         │ Publish to channel
         ▼
┌──────────────────┐
│ Redis Pub/Sub    │
└────────┬─────────┘
         │ Forward to subscribers
         ▼
┌──────────────────┐
│ Soketi Server    │
└────────┬─────────┘
         │ WebSocket connection
         ▼
┌──────────────────┐
│ Frontend JS      │
│ (Echo client)    │
└────────┬─────────┘
         │ Receive message
         ▼
┌──────────────────┐
│ Livewire         │
│ - Update state   │
│ - Re-render      │
└──────────────────┘
```

## File Upload Flow

```
┌──────────────┐
│ User Browser │
└──────┬───────┘
       │ 1. POST file via Livewire
       ▼
┌────────────────────┐
│ Livewire Component │
└──────┬─────────────┘
       │ 2. Validate file
       ▼
┌────────────────────┐
│ Storage Facade     │
└──────┬─────────────┘
       │ 3. Store file
       ▼
┌────────────────────┐
│ Filesystem         │
│ - Local (dev)      │
│ - S3 (production)  │
└──────┬─────────────┘
       │ 4. Return path
       ▼
┌────────────────────┐
│ Database Record    │
│ - Save file path   │
│ - Save metadata    │
└──────┬─────────────┘
       │ 5. Return success
       ▼
┌──────────────┐
│ User Browser │
└──────────────┘
```

## Cache Flow

### Read-Through Cache

```
┌─────────────┐
│ Request     │
└──────┬──────┘
       │ 1. Get server metrics
       ▼
┌─────────────┐
│ Check Cache │
└──────┬──────┘
       │
   ┌───┴───┐
   │Found? │
   └───┬───┘
       │
    Yes│    No
       │     │
       │     ▼
       │  ┌──────────────┐
       │  │ Fetch from   │
       │  │ Source       │
       │  └──────┬───────┘
       │         │ 3. Store in cache
       │         ▼
       │  ┌──────────────┐
       │  │ Redis Cache  │
       │  └──────┬───────┘
       │         │
       ▼         ▼
┌──────────────────┐
│ Return Data      │
└──────────────────┘
```

### Cache Invalidation

```php
// When application is updated
Application::updated(function ($application) {
    Cache::forget("application.{$application->id}");
    Cache::tags(['applications', "team.{$application->team_id}"])->flush();
});
```

## Queue Job Flow

```
┌─────────────────┐
│ Job Dispatch    │
│ Job::dispatch() │
└────────┬────────┘
         │ 1. Serialize job
         ▼
┌─────────────────┐
│ Queue Driver    │
│ (Redis)         │
└────────┬────────┘
         │ 2. Store in queue
         ▼
┌─────────────────┐
│ Horizon Worker  │
│ - Picks up job  │
└────────┬────────┘
         │ 3. Unserialize
         ▼
┌─────────────────┐
│ Job Handler     │
│ handle() method │
└────────┬────────┘
         │ 4. Execute logic
         ▼
    ┌────┴─────┐
    │ Success? │
    └────┬─────┘
         │
      Yes│    No
         │     │
         │     ▼
         │  ┌──────────────┐
         │  │ Failed Jobs  │
         │  │ Table        │
         │  └──────────────┘
         │
         ▼
┌─────────────────┐
│ Complete        │
└─────────────────┘
```

## Environment Variable Flow

```
┌──────────────┐
│ User Input   │
│ (UI/API)     │
└──────┬───────┘
       │ 1. Submit env vars
       ▼
┌──────────────────────┐
│ Validation           │
│ - Format check       │
│ - No invalid chars   │
└──────┬───────────────┘
       │ 2. Validated
       ▼
┌──────────────────────┐
│ Encryption           │
│ encrypt($value)      │
└──────┬───────────────┘
       │ 3. Encrypted value
       ▼
┌──────────────────────┐
│ Database Storage     │
│ environment_vars     │
└──────┬───────────────┘
       │ 4. During deployment
       ▼
┌──────────────────────┐
│ Decryption           │
│ decrypt($value)      │
└──────┬───────────────┘
       │ 5. Plain value
       ▼
┌──────────────────────┐
│ Docker Compose       │
│ environment:         │
│   - KEY=value        │
└──────┬───────────────┘
       │ 6. Container start
       ▼
┌──────────────────────┐
│ Container Env        │
│ process.env.KEY      │
└──────────────────────┘
```

## SSH Command Execution Flow

```
┌───────────────┐
│ Zpanel        │
└───────┬───────┘
        │ 1. executeRemoteCommand($command)
        ▼
┌───────────────────┐
│ SSH Connection    │
│ - Load private key│
│ - Decrypt key     │
└───────┬───────────┘
        │ 2. ssh2_connect()
        ▼
┌───────────────────┐
│ Target Server     │
└───────┬───────────┘
        │ 3. Execute command
        ▼
┌───────────────────┐
│ Command Output    │
│ - stdout          │
│ - stderr          │
│ - exit code       │
└───────┬───────────┘
        │ 4. Return to Zpanel
        ▼
┌───────────────────┐
│ Log & Process     │
│ - Store logs      │
│ - Update status   │
└───────────────────┘
```

## Monitoring Data Flow

```
┌──────────────────┐
│ Server           │
└────────┬─────────┘
         │ Metrics collection
         ▼
┌──────────────────┐
│ Server Check Job │
│ (Cron: every 5m) │
└────────┬─────────┘
         │ SSH: docker stats
         ▼
┌──────────────────┐
│ Parse Metrics    │
│ - CPU usage      │
│ - Memory usage   │
│ - Disk usage     │
└────────┬─────────┘
         │ Store in database
         ▼
┌──────────────────┐
│ ServerMetrics    │
│ Table            │
└────────┬─────────┘
         │ Display
         ▼
┌──────────────────┐
│ Dashboard        │
│ (Livewire)       │
└──────────────────┘
```

## Team-Based Data Isolation

```
┌──────────┐
│ Request  │
└─────┬────┘
      │ 1. Authenticated user
      ▼
┌─────────────────┐
│ User Model      │
│ - current_team  │
└─────┬───────────┘
      │ 2. Get current team
      ▼
┌─────────────────┐
│ Global Scope    │
│ (Model boot)    │
└─────┬───────────┘
      │ 3. WHERE team_id = X
      ▼
┌─────────────────┐
│ Query Builder   │
└─────┬───────────┘
      │ 4. Filtered query
      ▼
┌─────────────────┐
│ Database        │
└─────┬───────────┘
      │ 5. Team-scoped results
      ▼
┌─────────────────┐
│ Response        │
└─────────────────┘
```

## Backup Data Flow

```
┌──────────────────┐
│ Scheduled Task   │
│ (Cron: daily)    │
└────────┬─────────┘
         │ 1. Trigger backup job
         ▼
┌──────────────────────┐
│ DatabaseBackupJob    │
└────────┬─────────────┘
         │ 2. SSH to server
         ▼
┌──────────────────────┐
│ Execute pg_dump      │
│ or mysqldump         │
└────────┬─────────────┘
         │ 3. Dump file created
         ▼
┌──────────────────────┐
│ Compress (gzip)      │
└────────┬─────────────┘
         │ 4. Upload to storage
         ▼
┌──────────────────────┐
│ S3-Compatible        │
│ Storage              │
│ - AWS S3             │
│ - MinIO              │
│ - Backblaze B2       │
└────────┬─────────────┘
         │ 5. Verify upload
         ▼
┌──────────────────────┐
│ Update Backup Record │
│ - File size          │
│ - Location           │
│ - Checksum           │
└────────┬─────────────┘
         │ 6. Cleanup local file
         ▼
┌──────────────────────┐
│ Send Notification    │
│ - Email              │
│ - Discord/Slack      │
└──────────────────────┘
```

## Cloudflare Integration Data Flow

```
┌──────────────────┐
│ Domain Config    │
│ Change           │
└────────┬─────────┘
         │ 1. User updates FQDN
         ▼
┌──────────────────────┐
│ Application Updated  │
│ Event                │
└────────┬─────────────┘
         │ 2. Listener triggered
         ▼
┌──────────────────────┐
│ CloudflareSyncJob    │
└────────┬─────────────┘
         │ 3. Call Cloudflare API
         ▼
┌──────────────────────┐
│ Cloudflare API       │
│ - Create DNS record  │
│ - Configure SSL      │
└────────┬─────────────┘
         │ 4. Return status
         ▼
┌──────────────────────┐
│ Update App Status    │
│ - DNS configured     │
│ - SSL active         │
└────────┬─────────────┘
         │ 5. Notify user
         ▼
┌──────────────────────┐
│ Notification         │
│ - UI toast           │
│ - Email (optional)   │
└──────────────────────┘
```

## Error Propagation Flow

```
┌──────────────┐
│ Exception    │
│ Thrown       │
└──────┬───────┘
       │ 1. Uncaught exception
       ▼
┌────────────────────┐
│ Exception Handler  │
│ app/Exceptions/    │
└──────┬─────────────┘
       │ 2. Log exception
       ▼
┌────────────────────┐
│ Logging System     │
│ - File logger      │
│ - Sentry (prod)    │
└──────┬─────────────┘
       │ 3. Report error
       ▼
┌────────────────────┐
│ Error Monitoring   │
│ - Sentry dashboard │
│ - Logs             │
└──────┬─────────────┘
       │ 4. Return response
       ▼
┌────────────────────┐
│ User Response      │
│ - Error message    │
│ - HTTP 500         │
│ - Error ID         │
└────────────────────┘
```

## Additional Resources

- [System Architecture](system-overview.md)
- [Deployment Flow](deployment-flow.md)
- [Database Patterns](../../implementation/phase-1/Zpanel/.cursor/rules/database-patterns.mdc)
- [API & Routing](../../implementation/phase-1/Zpanel/.cursor/rules/api-and-routing.mdc)

