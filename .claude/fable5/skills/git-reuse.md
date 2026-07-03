PR + BRANCH REUSE POLICY

────────────────────────────────────────
CORE PRINCIPLE
────────────────────────────────────────
Existing work is authoritative.

If a branch exists in:
underdogg-forks/invoiceplane-v2

and is linked to a PR in:
invoiceplane/invoiceplane-v2

it MUST be reused.

────────────────────────────────────────
MAPPING RULE
────────────────────────────────────────
Issue ID → PR → Branch → Fork repository state

This mapping is immutable during execution.

────────────────────────────────────────
NO DUPLICATION RULE
────────────────────────────────────────
Never:
- recreate PR branch
- reinitialize git history
- reapply already existing commits
