# Export Architecture

## Overview

This application uses Filament's export system, which handles exports **asynchronously via queued jobs**.

**⚠️ Queue Worker Required**: Export functionality requires a running queue worker to process export jobs.

## Queue Configuration

### Local Development

For local development, you can use the `sync` queue driver:

```bash
# In .env
QUEUE_CONNECTION=sync
```

Or run a queue worker in a separate terminal:

```bash
php artisan queue:work
```

### Production

For production environments, configure a proper queue driver:

**Redis (Recommended):**
```bash
# In .env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Database:**
```bash
# In .env
QUEUE_CONNECTION=database

# Run migration
php artisan queue:table
php artisan migrate
```

**Supervisor Configuration:**

Use Supervisor to keep queue workers running:

```ini
[program:invoiceplane-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

## Database Storage

**Important**: The `exports` table is managed by Filament and is used **only for internal job coordination**. Export records are temporary and serve these purposes:

1. **Job Coordination**: Track export progress across multiple queue jobs
2. **File Management**: Store temporary file paths until download
3. **Notification**: Send completion notifications to users

**The exports table is NOT meant for long-term storage or export history.**

## Export Lifecycle

1. User initiates export → Export record created
2. Jobs dispatched to queue → Export record tracks progress
3. File generated → Export record stores file path
4. User downloads file → Export record remains temporarily
5. **Automatic Cleanup**: Filament's Export model uses the `Prunable` trait and will be automatically deleted by Laravel's model pruning system

## Testing

Tests use `Queue::fake()` and `Storage::fake()` to avoid actual database/file operations:

```php
Queue::fake();
Storage::fake('local');

// Act
Livewire::actingAs($this->user)
    ->test(ListExpenses::class)
    ->callAction('exportCsvV2', data: [...]);

// Assert - verify job dispatching, not database records
Bus::assertChained([...]);
```

## Configuration

### Queue Worker

Exports will not process without a queue worker running. Choose one of these options:

**Option 1: Sync Driver (Local Development Only)**
```bash
# In .env
QUEUE_CONNECTION=sync
```
This processes jobs immediately but blocks the request.

**Option 2: Queue Worker (Recommended)**
```bash
# Run in separate terminal
php artisan queue:work

# Or with specific options
php artisan queue:work --queue=default --sleep=3 --tries=3
```

**Option 3: Supervisor (Production)**

See configuration example above.

### Model Pruning

To automatically clean up old export records, run Laravel's model pruning command:

```bash
php artisan model:prune
```

This should be scheduled to run daily in production (add to your task scheduler):

```php
// In routes/console.php or bootstrap/app.php
Schedule::command('model:prune')->daily();
```

## No Export History

By design, there is **no export history feature**. Users can export data when needed, download it immediately, and the system automatically cleans up the temporary records. This approach:

- ✅ Reduces database bloat
- ✅ Improves privacy (no lingering export data)
- ✅ Simplifies the system
- ✅ Follows the principle: "I don't need to see what I exported in the past"
