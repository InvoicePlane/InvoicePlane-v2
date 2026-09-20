---
name: e2e-flake-prevention
description: How to tell a real E2E bug from environment flakiness in this project, and how to prevent both — timeout defaults, shared auth state, and load-induced timing
license: MIT
metadata:
  author: project
---

# Purpose

A flaky test is not "fine, just rerun it." Every flake in this project's history so far has had
one of two causes: a genuinely bad locator/assertion that only fails under specific timing (a
real bug wearing a flaky costume), or a known, understood environment characteristic (Xdebug
overhead under load) that has a concrete, permanent fix — not a shrug. This skill exists so
neither gets waved off as "just flaky."

---

# 1. The two failure classes look almost identical — check before assuming either

A test that fails intermittently could be:

- **A real, order-dependent bug.** `auth.spec.js`'s `logout()` call was invalidating the
  server-side session that the whole suite's shared `storageState` (`auth.json`) represented —
  every test that happened to run *after* it in the same invocation would silently start
  unauthenticated. This looked exactly like flakiness (passed alone, failed sometimes in the full
  suite) and was not — it was 100% deterministic given execution order, and execution order isn't
  guaranteed stable.
- **Genuine environment-load timing.** Every PHP request in this dev environment pays Xdebug
  step-debug connection overhead (visible as `Could not connect to debugging client` on literally
  every request in this stack's logs). Under sustained load — several full-suite runs back to
  back, or many parallel workers logging in as the same user simultaneously — individual requests
  occasionally exceed a 30s default timeout even though the feature being tested works correctly.

**The diagnostic that tells them apart:** rerun the exact same test in isolation, then rerun the
full suite twice more. A real order-dependent bug fails **every time** it runs after the
triggering test/condition, regardless of load. Environment-load flakiness fails **rarely and
inconsistently**, always at a generic wait/timeout step (never at a specific assertion about
page content), and passes cleanly most of the time including under load. If you can't tell which
one you're looking at from one failure, you don't have enough evidence yet — get a second data
point before deciding.

---

# 2. Known cause in this project: Xdebug overhead under sustained load

This dev container has Xdebug step-debug enabled, and it attempts (and fails) to connect on
every single PHP request — `Xdebug: [Step Debug] Could not connect to debugging client`. That
failed connection attempt adds real, compounding latency. Symptoms specific to this cause:

- Failures cluster at `page.waitForURL(...)` / login steps, never at content assertions.
- Failures appear more often after several consecutive full-suite invocations in a short window,
  not on a fresh single run.
- The exact same test passes cleanly when rerun shortly after.

This is not something to "fix" by disabling Xdebug (it's presumably there on purpose for
interactive debugging) — the correct response is generous, explicit timeouts on the specific
waits known to be affected, not a blanket global timeout bump that masks other problems.

---

# 3. The recurring pitfall: a default/hardcoded timeout silently outranks your config bump

This has happened twice in this project already. Playwright's per-call timeouts (the second
argument to `waitForURL`, `waitForSelector`, etc.) **always override** whatever the global
`timeout` in `playwright.config.js` says — bumping the config's `timeout` value does nothing for
a call that already has, or defaults to, its own timeout:

- `testrunner`'s generated interaction specs had a literal `{ timeout: 30000 }` hardcoded into
  `page.waitForURL(...)` — bumping the config's global `timeout` to 60000 had zero effect until
  the hardcoded literal itself was changed.
- `global-setup.js`'s `page.waitForURL(...)` had **no** explicit timeout at all, silently
  inheriting Playwright's built-in 30s default — again, unaffected by the config's `timeout`
  value, because `globalSetup` doesn't run inside a test and isn't bound by the test `timeout`
  option at all.

**Rule:** any `waitForURL`/`waitForSelector`/action call that's a known or plausible
load-sensitive chokepoint (logins, redirects after a mutating action, anything in `globalSetup`)
needs its **own explicit timeout**, not a config-level setting. Comment *why* the number is what
it is (e.g. "60s, not the 30s default — every request here pays Xdebug overhead") so the next
person doesn't "clean it up" back to a bare call.

---

# 4. Practical checklist before calling something flaky (or fixed)

- Rerun the failing test in isolation. Passes alone, fails only in the full suite → suspect
  shared state (auth, database records, singleton fixtures), not timing. Investigate execution
  order and what upstream tests mutate.
- Rerun the full suite at least twice more before concluding a fix worked. One green run after a
  fix is not proof — this project has already seen a fix work once and then reveal the same class
  of bug again one layer deeper (the 30s→60s config bump that didn't touch the hardcoded literal).
- Don't run many full-suite invocations back-to-back with no gap when diagnosing something
  unrelated — it compounds load-induced timing issues and makes real signal harder to read. A
  short pause between full runs, or running a narrower `-g` filter while iterating, keeps noise
  down.
- If a failure is *specifically* at a wait/timeout step with no content-related error message,
  check whether it's touching a chokepoint from §3 before assuming it's a new bug.
- If a failure has a specific, content-related error message (a wrong selector, an unexpected
  page, a validation error) — that's real, full stop, regardless of how it presented. Don't retry
  your way past it.
