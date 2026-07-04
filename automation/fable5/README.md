# Fable5 Automation Framework

Standalone automation framework for Fable5.

## Structure

- `bin/`: Executable scripts.
- `bootstrap/`: Framework bootstrapping.
- `config/`: Runtime configuration.
- `src/`: Source code.
- `storage/`: Logs and cache.
- `tests/`: PHPUnit tests.

### Capabilities

The framework provides both REST, CLI, and GraphQL interactions with GitHub.

### REST Capabilities (`GitHubClient`)

- **Pull Request Management**: Create, get, and list pull requests.
- **Issue Management**: Create, get, update, and list issues; add comments.
- **Workflow Inspection**: List runs (with pagination/generators), get run details, list jobs, delete runs.
- **Repository Management**: Get, update, and delete repositories; manage topics.
- **Forking**: Create and get forks via `ForkRepositoryClient`.

### CLI Capabilities (`GitHubCli`)

- **Workflow Orchestration**: List, rerun, get logs, and bulk delete workflow runs.
- **Issue CLI**: `gh issue` list and create.
- **PR CLI**: `gh pr` list, create, and merge.
- **Project CLI**: `gh project` list and view.

### GraphQL Capabilities (`GitHubGraphQLClient`)

- **Relational Queries**: Fetch issues with comments and labels, fetch ProjectV2 with items, and fetch workflow runs via check suites.
- **Mutations**: Add items to ProjectV2.
- **Custom Queries**: Generic `query()` method for any GraphQL operation.

### Missing Capabilities

Below is a summary of missing capabilities that may be required for future expansion.

#### Missing REST Capabilities (`GitHubClient`)

- **Git Data API**: Low-level access to blobs, trees, and commits (outside of standard PR/Repo methods).
- **Actions Secrets & Variables**: Management of repository or environment secrets.
- **Organization & Team Management**: Managing members, teams, and permissions.
- **Releases & Tags**: Creating releases or managing git tags.
- **Checks API**: Fine-grained control over Check Runs and Check Suites.

#### Missing `gh` CLI Capabilities (`GitHubCli`)

- **Release CLI**: `gh release` commands (create, download, upload).
- **Secret CLI**: `gh secret` commands (set, list, remove).
- **Gist CLI**: `gh gist` commands.
- **Variable CLI**: `gh variable` commands.
- **Search CLI**: `gh search` commands.

## Usage

The framework bootstraps Laravel but remains isolated from its autoloader.

```bash
php bin/fable5
```
