CONCURRENCY RULES

────────────────────────────────────────
PARALLEL EXECUTION
────────────────────────────────────────
Allowed only when:
- branches belong to different PRs
- no shared module writes

────────────────────────────────────────
MODULE LOCKING
────────────────────────────────────────
A module is locked when:
- a branch is actively modifying it

No concurrent edits allowed on:
- same Service
- same DTO
- same Filament Resource

────────────────────────────────────────
SAFE PARALLEL MODEL
────────────────────────────────────────
Each PR branch is an isolated execution unit.

No cross-branch writes to same module.
