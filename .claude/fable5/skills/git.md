GIT EXECUTION RULES

────────────────────────────────────────
BRANCH DISCOVERY
────────────────────────────────────────
Always resolve branches in this order:

1. GitHub PR branch reference
2. local fork (underdogg-forks/invoiceplane-v2)
3. remote origin fallback

Never create a branch if a PR-linked branch exists.

────────────────────────────────────────
BRANCH CHECKOUT RULE
────────────────────────────────────────
When PR exists:
- fetch PR head ref
- checkout exact branch
- continue history

No rebase unless explicitly required by issue.

────────────────────────────────────────
COMMIT RULES
────────────────────────────────────────
- atomic commits only
- one logical change per commit
- frequent commits required

────────────────────────────────────────
SAFETY RULE
────────────────────────────────────────
Never overwrite branch history that already belongs to a PR.
