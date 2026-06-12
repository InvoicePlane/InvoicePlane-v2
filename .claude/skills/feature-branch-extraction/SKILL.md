---
name: feature-branch-extraction
description: "Extracts a set of files from develop into a dedicated feature branch so the feature branch is ahead of develop and a PR can be opened. Activates whenever the user asks to move, extract, or isolate files from develop into a feature branch, or when a branch is created before files are removed from develop."
license: MIT
metadata:
  author: project
---

# Feature Branch Extraction

## The Problem

When you create a feature branch **before** removing its files from `develop`, the branch ends up *behind* develop after the removal commit lands. The branch and develop share the same history, so there is no diff and no PR can be opened.

```
develop:    A → B → C (removes files)
feature/X:  A → B          ← behind develop, nothing to PR
```

## Correct Procedure

Always follow these steps in order.

### 1. Identify the extraction commit on develop

Find the SHA of the commit on `develop` that removed the files (call it `REMOVE_SHA`) and the SHA just before it that still has the files (call it `BEFORE_SHA`):

```bash
git log --oneline develop | head -10
# REMOVE_SHA  chore: extract X to feature/X
# BEFORE_SHA  ran pint / previous commit
```

### 2. Create the feature branch pointing at REMOVE_SHA

If the branch does not exist yet:

```bash
git checkout -b feature/X
git push -u origin feature/X
git checkout develop
```

If the branch already exists but is behind develop (the mistake already happened):

```bash
git checkout feature/X
git reset --hard REMOVE_SHA   # move tip to develop's current HEAD
```

### 3. Restore the extracted files onto the feature branch

While on `feature/X`, check out every file/directory that was removed in REMOVE_SHA from BEFORE_SHA:

```bash
git checkout BEFORE_SHA -- path/to/file1 path/to/dir/ ...
```

This stages the files as additions on top of develop's current state.

> Also restore any files that were *modified* by the removal commit (e.g. List pages
> that had export actions stripped, or a ServiceProvider that had a command registration
> removed). Check these out from BEFORE_SHA too so the feature branch has the full
> original version.

### 4. Commit and force-push

```bash
git commit -m "feat: add <feature name>"
git push --force-with-lease origin feature/X
```

After this, the graph looks like:

```
develop:    A → B → REMOVE_SHA
feature/X:  A → B → REMOVE_SHA → NEW_SHA (adds files)
```

`feature/X` is one commit ahead of `develop`, and a PR can be opened.

## Checklist

- [ ] Identified REMOVE_SHA (the deletion commit on develop)
- [ ] Identified BEFORE_SHA (the commit before the deletion)
- [ ] Feature branch tip is at REMOVE_SHA (`git reset --hard REMOVE_SHA`)
- [ ] All deleted files restored with `git checkout BEFORE_SHA -- <paths>`
- [ ] All modified files (stripped pages, providers) also restored from BEFORE_SHA
- [ ] New commit created on feature branch
- [ ] Force-pushed to remote (`--force-with-lease`)
- [ ] PR can be opened (feature branch is ahead of develop)

## Anti-patterns to avoid

- **Creating the feature branch first, then removing from develop.** This always produces a branch that is behind develop.
- **`git checkout BEFORE_SHA -- .`** (restoring the entire tree). This would make the feature branch identical to BEFORE_SHA and reintroduce all the commits develop made after that point.
- **Merging develop into feature/X** to catch up. This produces a merge commit and pollutes the diff with unrelated changes.
