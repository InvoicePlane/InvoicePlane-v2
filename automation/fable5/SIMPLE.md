Think of this whole system like a **factory that turns GitHub issues into finished pull requests automatically**.

Each file is one worker in that factory. Nothing overlaps. Each one has one job.

---

# 1. `bin/fable5` → “The power button”

**Location:**

```text
automation/fable5/bin/fable5
```

### What it does

This is what you click or run in terminal.

It:

* starts the system
* wires all parts together
* tells the factory to begin

### Think of it like:

> The ON button of a machine

### It should NOT:

* do logic
* decide anything
* process issues

It only says:

> “Start the system with these tools.”

---

# 2. `Fable5Kernel` → “The manager”

**Location:**

```text
automation/fable5/src/Execution/Fable5Kernel.php
```

### What it does

This is the **boss of the factory floor**.

It:

* receives the list of issues
* asks other parts to organize them
* starts execution
* coordinates everything

### Think of it like:

> A factory manager giving orders

### It should NOT:

* talk to GitHub directly
* run git commands
* process HTTP requests

It only says:

> “Here are the tasks. Organize and execute them.”

---

# 3. `PRBranchReconciler` → “The matcher”

**Location:**

```text
automation/fable5/src/Indexer/PRBranchReconciler.php
```

### What it does

This part looks at:

* GitHub issues
* existing pull requests
* existing branches

And answers:

> “What already exists, and what needs to be created?”

### Think of it like:

> A librarian checking what books already exist

### Output example:

* Issue #12 already has a PR → reuse branch
* Issue #13 has nothing → create new work
* Issue #14 is part of same feature → group it

---

# 4. `GitHubClient` → “The GitHub brain”

**Location:**

```text
automation/fable5/src/Clients/GitHubClient.php
```

### What it does

This talks to GitHub API and understands:

* issues
* pull requests
* branches
* workflow status (if needed)

### Think of it like:

> Someone who speaks “GitHub language”

It does NOT:

* decide what to do
* plan execution

It only answers questions like:

> “What does GitHub currently look like?”

---

# 5. `GitHubHttpTransport` → “The delivery truck”

**Location:**

```text
automation/fable5/src/Http/GitHubHttpTransport.php
```

### What it does

This is the lowest level.

It only:

* sends HTTP requests
* handles authentication
* retries failed requests
* respects rate limits

### Think of it like:

> A truck delivering letters to GitHub

It does NOT:

* understand issues
* understand PRs
* understand logic

It only knows:

> “Send request → get response”

---

# 6. `PullRequestManager` → “The PR worker”

**Location:**

```text
automation/fable5/src/Git/PullRequestManager.php
```

### What it does

This handles PR-specific operations:

* create PR
* update PR
* check PR status
* link branch ↔ PR

### Think of it like:

> The person who only works with pull requests

Not GitHub in general. Just PRs.

---

# 7. `ExecutionPlanner` → “The strategist”

### What it does

Takes all issues and decides:

* what order to do them in
* which can run in parallel
* which depend on others

### Think of it like:

> The chess player planning 10 moves ahead

---

# 8. `ExecutionScheduler` → “The traffic controller”

### What it does

Takes the plan and decides:

* what runs now
* what waits
* what runs in parallel safely

### Think of it like:

> Air traffic control at an airport

---

# 9. `ExecutionRunner` → “The worker doing the actual work”

### What it does

This is the part that actually:

* creates branches
* pushes commits
* opens PRs
* modifies code

### Think of it like:

> The mechanic fixing the car

---

# 10. `ExecutionGraph` / `ExecutionNode` → “The map”

### What they do

They represent work like a diagram:

* each issue = a node
* dependencies = arrows between nodes

### Think of it like:

> A roadmap of everything the factory must do

---

# Whole system in one picture

```
bin/fable5
    ↓
Fable5Kernel
    ↓
PRBranchReconciler (figures out state)
    ↓
ExecutionPlanner (decides order)
    ↓
ExecutionScheduler (controls flow)
    ↓
ExecutionRunner (does work)
    ↓
GitHubClient (asks GitHub things)
    ↓
GitHubHttpTransport (sends requests)
```

---

# Simple way to remember it

* **bin/** = start button
* **Kernel** = manager
* **Planner** = thinker
* **Scheduler** = traffic control
* **Runner** = worker
* **Clients** = talk to GitHub
* **Transport** = sends requests

---

If you understand this structure, you already understand something most production automation systems get wrong:

> separating thinking, planning, and execution so nothing becomes chaotic or unsafe.
