---
name: service-layer
description: Defines application service structure and business orchestration boundaries
license: MIT
metadata:
  author: project
---

# Service Layer

Services define **business orchestration only**.

They are framework-agnostic and represent the application’s business operations.

---

# 1. Responsibility

Services MUST:

- contain business logic
- coordinate models and repositories
- enforce domain rules
- return models or DTOs

Services MUST NOT:

- use Filament
- use HTTP layer
- depend on request/response objects
- contain UI logic
- use service locators (`app()`, `resolve()`)

---

# 2. Dependency Rule

Services MUST use constructor injection:

```php
public function __construct(
    private InvoiceRepository $repository
) {}
```

No `app()` or service locator usage inside services.

---

# 3. Filament Boundary Rule

Filament is a UI boundary layer with different lifecycle constraints.

Allowed patterns:

### Pages / Resources
- constructor injection preferred
- `app(Service::class)` allowed as fallback

### Table Actions / Closures
- `app(Service::class)` is allowed
- constructor injection is not guaranteed in closures

Example:

```php
Action::make('create')
    ->action(function (array $data) {
        app(InvoiceService::class)->createInvoice($data);
    });
```

This is acceptable UI-layer coupling.

---

# 4. Standard Service Shape

```
Modules/{Name}/src/Services/{Model}Service.php
```

---

# 5. Standard Methods

- createX
- updateX
- deleteX
- findOrFail
- listForCompany

---

# 6. Core Principle

Services are pure business units.

They must not depend on framework execution context.

UI layers may use service locator as a pragmatic boundary escape hatch.
