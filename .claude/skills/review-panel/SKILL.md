---
name: review-panel
description: Free, local multi-agent code review — fans the current diff (or a PR/branch/path) out across parallel correctness, security, simplification, and test-coverage lenses using ordinary subagents, then merges their findings into one deduplicated report. A self-hosted alternative to the billed cloud "ultra" review.
---

# Skill: review-panel

Runs a multi-angle code review by spawning several parallel `lens-reviewer` subagents against the same diff, each auditing from one distinct angle, then merges their findings into a single deduplicated, severity-ranked report via `ReportFindings`. Uses only regular subagent calls — no separate billed cloud job.

## Inputs

`$ARGUMENTS` (optional) — one of:
- empty: review the current diff
- a PR number (`123` or `#123`)
- a branch name
- a file/directory path (scope the review to that path)

## Step 1 — Resolve the target and get the diff

- Empty argument: find the diff to review. Try, in order: `git merge-base --fork-point <default-branch> HEAD` against the diff, then fall back to `origin/HEAD`/`origin/main`/`origin/develop` as base; if the branch has no clear base, use the working-tree diff (`git diff HEAD`).
- PR number: if `gh` is available and the repo has a GitHub remote, use `gh pr diff <n>`.
- Branch name: diff that branch against its merge-base with the default branch.
- Path: scope the diff (or, if the path has no pending changes, the file content itself) to that path.

Print the resolved target and a one-line `--stat` summary before continuing. If the diff is empty, say so and stop — don't spawn agents for nothing.

## Step 2 — Fan out to lenses

Spawn all of the following in **one message** (multiple `Agent` tool calls in a single response, so they run in parallel), with `subagent_type: lens-reviewer`. Give every agent the same diff (paste it inline, or the exact command to reproduce it, plus the repo root path) and exactly ONE lens:

1. **Correctness & bugs** — logic errors, edge cases, off-by-one, null/undefined handling, race conditions, error-handling gaps.
2. **Security** — injection, authz/authn gaps, secrets, unsafe deserialization, SSRF, path traversal. If a `security-review-checklist` or `security-review` skill exists in this environment, tell the agent to use it.
3. **Simplification, reuse & efficiency** — dead code, unneeded abstraction, duplicated logic, obvious performance issues.
4. **Test coverage** — missing tests for new/changed behavior, weak assertions, tests that would still pass if the logic were wrong.

Add a 5th **architecture/consistency** lens only when the repo has relevant project-specific convention skills available (e.g. `application-architecture-standard`, `service-layer`, `dto-contract`, `data-layer-contracts`, `laravel-modules`) — tell that agent which ones to check the diff against.

Every lens agent's prompt must include the diff/target, its ONE assigned lens, and end with:

> Verify every finding before reporting it — reproduce it or trace the exact failure path in the actual (non-diff-truncated) file. Call `ReportFindings` exactly once with your verified findings, most severe first (empty array if nothing survives verification). Do not report anything you have not personally verified.

## Step 3 — Wait, then merge

Wait for all lens agents to finish (you'll get completion notifications — do not poll or read their transcripts directly). Once all are back:

- Pool every finding from every lens.
- Dedupe: findings at the same file+line, or clearly describing the same underlying issue from different angles, collapse into one — keep the strongest verdict and the clearer `failure_scenario`.
- Drop anything without a verified `CONFIRMED`/`PLAUSIBLE` verdict.
- Sort most-severe-first (correctness/security defects above style/simplification nits).
- Call `ReportFindings` yourself exactly once with the final merged list. This is the skill's actual output — don't also restate the findings as prose.

## Notes

- This mirrors what a multi-reviewer cloud pass does (several independent reviewers, parallel, structured findings) but runs entirely as ordinary subagents inside the current session, billed the same as any other subagent work — there is no separate paid job.
- For a quick/cheap single-pass review, use `/code-review` directly instead; reach for `/review-panel` when the extra parallel depth is worth the extra tokens.
