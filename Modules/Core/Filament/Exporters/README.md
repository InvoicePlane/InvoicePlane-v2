# Export Architecture

## Overview

This application uses Filament's export system, which handles exports asynchronously via queued jobs.

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

To automatically clean up old export records, run Laravel's model pruning command:

```bash
php artisan model:prune
```

This should be scheduled to run daily in production (add to your task scheduler).

## No Export History

By design, there is **no export history feature**. Users can export data when needed, download it immediately, and the system automatically cleans up the temporary records. This approach:

- ✅ Reduces database bloat
- ✅ Improves privacy (no lingering export data)
- ✅ Simplifies the system
- ✅ Follows the principle: "I don't need to see what I exported in the past"
