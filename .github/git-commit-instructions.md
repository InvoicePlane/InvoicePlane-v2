## Commit message rules
- Use the conventional commit format: `<type>(<scope>): <description>`
- Types: feat, fix, docs, style, refactor, test, chore, perf
- Keep the description concise (under 50 characters)
- Use imperative mood (e.g., "add" not "added" or "adds")
- Don't end with a period
- Use lowercase for the first word unless it's a proper noun
- Provide more details in the commit body if needed, separated by a blank line

## Branch naming conventions
- Use kebab-case (lowercase with hyphens)
- Follow the pattern: `<type>/<issue-number>-<short-description>`
- Types: feature, bugfix, hotfix, release, support
- Example: `feature/123-add-dark-mode`

## Pull request guidelines
- Link related issues using keywords (Fixes #123, Closes #456)
- Provide a clear description of changes
- Add screenshots for UI changes
- Ensure all CI checks pass before requesting review
- Keep PRs focused and small when possible