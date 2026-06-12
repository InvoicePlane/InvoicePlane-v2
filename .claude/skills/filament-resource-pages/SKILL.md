---
name: service-layer
description: Defines application service structure and business orchestration boundaries
license: MIT
metadata:
  author: project
---

# Service Layer

Services define business orchestration and are the primary boundary for business logic execution.

They are framework-agnostic and represent application behavior independent of UI or transport layers.

---

# 1. Responsibility

Services MUST:

- contain business logic
- coordinate models and repositories
- enforce domain rules
- return models or internally used DTOs
- encapsulate persistence logic

Services MUST NOT:

- use Filament
- depend on HTTP layer (requests/responses)
- contain UI logic
- use service locators (`app()`, `resolve()`)
- depend on transport concerns

---

# 2. Dependency Rule

Services MUST use constructor injection:

```php
public function __construct(
    private InvoiceRepository $repository
) {}
```

No service locator usage is allowed inside services.

---

# 3. DTO Rule (Refined)

DTOs are NOT required for UI → Service communication.

DTO usage depends on coupling, not layer type:

## DTOs are required when:
- crossing system boundaries (API, external integrations, queues)
- multiple consumers share a contract
- transformation logic must be standardized
- stability across versions is required

## DTOs are NOT required when:
- input originates from trusted UI layer (e.g. Filament Forms)
- single consumer exists
- payload is short-lived and not reused elsewhere

### Rule of thumb:
> DTOs exist to stabilize unstable or shared contracts, not to formalize trusted UI input.

Services MAY still use DTOs internally if they improve clarity or structure.

---

# 4. Filament Boundary Rule

Filament is a UI orchestration layer.

## Allowed usage:

### Pages / Resources
- constructor injection preferred
- `app(Service::class)` allowed as fallback when needed

### Table Actions / Closures
- `app(Service::class)` is allowed
- constructor injection is not guaranteed in closure scope

Example:

```php
Action::make('create')
    ->action(function (array $data) {
        app(InvoiceService::class)->createInvoice($data);
    });
```

This is acceptable boundary-layer behavior.

---

# 5. Standard Service Shape

```
Modules/{Name}/src/Services/{Model}Service.php
```

---

# 6. Standard Methods

- createX
- updateX
- deleteX
- findOrFail
- listForCompany

---

# 7. Core Principle

Services are pure business units.

They MUST remain independent of framework execution context.

UI layers may use service locator as a pragmatic escape hatch where DI is not available.
