# Package Update Report Generator

This script generates a readable package update report from yarn.lock changes.

## Purpose

When Yarn dependencies are updated via the automated workflow, this script analyzes the git diff of `yarn.lock` and generates a human-readable report showing:

1. **Direct Dependencies** - Packages explicitly listed in `package.json`
2. **Transitive Dependencies** - Dependencies of dependencies

## Output Format

The script generates a tree-like report with clear version transitions:

```
╔═══════════════════════════════════════════════════════════════╗
║                    Package Update Report                     ║
╚═══════════════════════════════════════════════════════════════╝

📦 DIRECT DEPENDENCIES (from package.json)
─────────────────────────────────────────────────────────────────

  ✓ vite
    7.3.0 → 7.4.0

  ✓ tailwindcss
    4.1.10 → 4.1.12


🔗 TRANSITIVE DEPENDENCIES (dependencies of dependencies)
─────────────────────────────────────────────────────────────────

  └─ esbuild
     0.27.1 → 0.27.2

  └─ rollup
     4.28.0 → 4.29.1


═════════════════════════════════════════════════════════════════
SUMMARY: 2 direct, 2 transitive (4 total)
═════════════════════════════════════════════════════════════════
```

## Usage

The script is automatically run by the `yarn-update.yml` GitHub Actions workflow. It can also be run manually:

```bash
# Run from the repository root
node .github/scripts/generate-package-update-report.cjs
```

### Requirements

- Node.js (the version used by the project)
- Git (for detecting changes in yarn.lock)
- Must be run from the repository root directory

## How It Works

1. Reads `package.json` to identify direct dependencies
2. Parses `git diff yarn.lock` to detect version changes
3. Categorizes each updated package as direct or transitive
4. Generates a formatted report with clear version transitions
5. Writes the report to `updated-packages.txt`

## Integration with Workflow

The script is called in the `yarn-update.yml` workflow after dependency updates:

```yaml
- name: Get updated packages
  if: steps.check-changes.outputs.changes_detected == 'true'
  run: |
    node .github/scripts/generate-package-update-report.cjs
```

The generated report is then included in the pull request description for easy review.

## Benefits

- **Readability**: Clean, scannable format vs. raw yarn.lock diff
- **Clarity**: Direct dependencies highlighted separately from transitive ones
- **Version Tracking**: Clear "from → to" notation for all updates
- **Consistency**: Similar to `yarn upgrade` output that developers are familiar with
