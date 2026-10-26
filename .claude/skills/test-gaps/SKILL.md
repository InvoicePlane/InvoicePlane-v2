---
name: test-gaps
description: Flags security- or correctness-critical logic (auth checks, guards, validation) added or changed without a test proving both its allow and its deny path
---

# Purpose

Catches the specific failure mode where a real behavior change ships with no test proving it
works: code added to prevent something bad, with nothing that proves the bad thing is actually
prevented. Triggered by this incident: an `abort_unless`/authorization guard was added to
`MyCompanies::switch` with zero test coverage — it could have been silently deleted or inverted
in a later change and nothing would fail.

This is narrower than `security-review` (which finds *missing* guards in code) and unrelated to
`test-honesty` (which is about schema/factory/seeder alignment). This skill assumes the guard
already exists and asks: is there a test that would fail if the guard were removed?

---

# 1. Trigger Conditions

Apply this check whenever a diff adds or modifies any of:

- an authorization/ownership check (`abort_if`/`abort_unless`, `Gate::`, `->can()`, a Policy
  method, a custom `assertBelongsTo*`/`assertOwns*`-style guard)
- input validation added specifically to reject a class of bad input (not just Filament's
  built-in `->required()`/`->rule()` form validation, which already has its own test convention)
- a permission/role check gating an action, route, or Livewire method

---

# 2. Coverage Rule

Every guard covered by Rule 1 needs **two** tests, not one:

- **Allow path**: the legitimate case still succeeds through the guard.
- **Deny path**: the guard actually blocks the illegitimate case — asserts the specific
  exception/response the guard produces, not just "doesn't crash."

A guard with only an allow-path test (or no test) is a gap: nothing would catch the guard being
weakened, removed, or silently made a no-op in a later refactor.

---

# 3. Test Placement Rule

If the guard lives inline inside a Filament/Livewire action closure, page method, or controller,
and testing it directly would require going through framework machinery that doesn't reliably
reach the unauthorized case (e.g. a table's own query already scopes out records the user
couldn't select in the first place, so a Feature test via `callTableAction()` never actually
exercises the deny path), that's a signal the check belongs in an extracted, directly-testable
method — a service method, a Policy, a dedicated class — not a reason to skip the deny-path test.

---

# 4. What This Skill Does NOT Do

- Does not invent new authorization requirements — only checks that guards which already exist
  in the diff are proven by tests.
- Does not replace `security-review`'s job of spotting where a guard is *missing* entirely.
- Does not apply to routine Filament form validation (`->required()`, `->rule()`, etc.) — that
  has its own established test conventions in this codebase and isn't the failure mode this
  skill targets.
