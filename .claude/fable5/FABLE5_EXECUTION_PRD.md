FABLE5 AUTONOMOUS EXECUTION PRD
InvoicePlane-v2

────────────────────────────────────────
PURPOSE
────────────────────────────────────────
Fable5 processes GitHub issues into draft PRs while reusing existing branches from:
underdogg-forks/invoiceplane-v2

PRs already exist in:
invoiceplane/invoiceplane-v2

Branches already exist in:
underdogg-forks/invoiceplane-v2

Fable5 must reconcile both systems.

────────────────────────────────────────
CRITICAL RULE
────────────────────────────────────────
NEVER CREATE NEW BRANCHES IF A PR-BOUND BRANCH ALREADY EXISTS.

Always reuse:
- existing PR branches
- existing fork branches

Branch identity is authoritative.

────────────────────────────────────────
SOURCE OF TRUTH PRIORITY
────────────────────────────────────────
1. Existing GitHub PR (invoiceplane/invoiceplane-v2)
2. Existing branch in fork (underdogg-forks/invoiceplane-v2)
3. Issue definition
4. Repository code state

────────────────────────────────────────
EXECUTION MODEL
────────────────────────────────────────
- Iterate through all provided issue IDs
- For each issue:
    - locate existing PR
    - extract associated branch from fork
    - checkout and continue work on that branch
    - do NOT reinitialize branch

────────────────────────────────────────
BRANCH REUSE RULE
────────────────────────────────────────
If PR exists:
- fetch PR branch from upstream or fork
- checkout branch locally
- continue commits

If PR does NOT exist:
- only then create new branch

────────────────────────────────────────
COMMIT POLICY
────────────────────────────────────────
- frequent commits required
- atomic logical changes only
- never mix multiple issues unless explicitly grouped

────────────────────────────────────────
PR POLICY
────────────────────────────────────────
- all PRs must remain DRAFT
- PR title format:
  [IP-{issueId}] description
- PR body must be updated, never replaced blindly
- preserve GitHub discussion history

────────────────────────────────────────
FAILURE HANDLING
────────────────────────────────────────
If branch cannot be found:
- attempt fetch from:
  underdogg-forks/invoiceplane-v2
- if still missing:
  skip issue and log reason

────────────────────────────────────────
EXECUTION END CONDITION
────────────────────────────────────────
Stop when:
- all issues processed OR skipped
