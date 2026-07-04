FABLE5 EXECUTION BOOT FLOW

────────────────────────────────────────
PHASE 0 — SYSTEM INITIALIZATION
────────────────────────────────────────
Before any issue execution begins, Fable5 MUST build a deterministic execution graph.

This step is mandatory and must complete successfully before any branch work starts.

────────────────────────────────────────
PHASE 1 — DATA COLLECTION
────────────────────────────────────────
Fetch the following sources:

1. All open PRs from:
   invoiceplane/invoiceplane-v2

2. All branches from:
   underdogg-forks/invoiceplane-v2

3. Input issue list (static execution payload)

────────────────────────────────────────
PHASE 2 — RECONCILIATION
────────────────────────────────────────
Build an ExecutionGraph by mapping:

Issue ID →
Existing PR →
Associated branch (if available) →
Fork branch state

Rules:

- If PR exists AND branch exists in fork:
  → mark node as EXISTING_PR

- If PR exists BUT branch missing:
  → mark node as PR_MISSING_BRANCH

- If branch exists BUT no PR:
  → mark node as ORPHAN_BRANCH

- If neither exists:
  → mark node as NEW

────────────────────────────────────────
PHASE 3 — EXECUTION STRATEGY GENERATION
────────────────────────────────────────
Fable5 MUST derive execution order from graph:

Priority order:
1. EXISTING_PR (reuse and continue work)
2. ORPHAN_BRANCH (recover and attach to PR if needed)
3. PR_MISSING_BRANCH (repair state)
4. NEW (create fresh branches)

────────────────────────────────────────
PHASE 4 — PARALLELIZATION PLAN
────────────────────────────────────────
Fable5 may execute branches in parallel only if:

- no shared module writes exist
- no overlapping DTO / Service modifications occur

Otherwise execution must be serialized per module lock rules.

────────────────────────────────────────
PHASE 5 — EXECUTION HANDOFF
────────────────────────────────────────
Only after graph is complete:

→ begin issue processing loop
→ reuse branches from graph
→ never recreate existing execution state
