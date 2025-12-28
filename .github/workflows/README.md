# GitHub Actions Workflows

This directory contains GitHub Actions workflows for automated CI/CD tasks.

## Available Workflows

### 1. Production Release (`release.yml`)

**Trigger:** Automatically runs on every push to the `master` branch

**Purpose:** Creates a production-ready release package of InvoicePlane v2 and publishes it as a GitHub Release

**What it does:**
1. **Downloads translations from Crowdin** - Retrieves the latest translations
2. **Builds frontend assets** - Runs `yarn install --frozen-lockfile && yarn build`
3. **Installs PHP dependencies** - Runs `composer install --no-dev` for production
4. **Cleans up node_modules** - Removes Node.js dependencies
5. **Optimizes vendor directory** - Removes unnecessary files (tests, docs, etc.)
6. **Creates release archive** - Packages everything into a timestamped ZIP file
7. **Generates version tag** - Creates a new version tag (alpha/beta/stable)
8. **Creates GitHub Release** - Publishes release with changelog and artifacts

**Release Types:**

The workflow supports configurable release types (set in workflow file):
- `alpha` - Pre-release versions (increments patch, adds -alpha suffix)
- `beta` - Beta versions (increments patch, adds -beta suffix)
- `stable` - Stable releases (increments minor version)

To change the release type, edit the `RELEASE_TYPE` environment variable at the top of `release.yml`.

**Versioning:**

The workflow automatically:
- Detects the latest tag (or starts from v0.0.0)
- Increments version based on release type
- Creates a new tag (e.g., v0.1.0-alpha, v0.2.0-beta, v1.0.0)
- Generates release notes showing changes since the previous tag

**Security:**

The workflow uses minimal permissions:
- `contents: write` - Required for creating releases and tags
- `actions: write` - Required for uploading workflow artifacts

**Required Secrets:**

Before using this workflow, you need to configure these GitHub secrets:

- `CROWDIN_PROJECT_ID` - Your Crowdin project ID
- `CROWDIN_PERSONAL_TOKEN` - Your Crowdin personal access token

To add these secrets:
1. Go to your repository Settings
2. Navigate to Secrets and variables → Actions
3. Click "New repository secret"
4. Add each secret with its corresponding value

**Crowdin Setup:**

To get your Crowdin credentials:
1. Log in to [Crowdin](https://crowdin.com/)
2. Navigate to your InvoicePlane project
3. Go to Settings → API
4. Generate a Personal Access Token
5. Copy your Project ID from the project settings

**Accessing Releases:**

After the workflow runs:
1. Go to the **Releases** section of your repository
2. Find the latest release (e.g., "Release v0.1.0-alpha")
3. Download the ZIP file and checksums from the release assets
4. Review the automated changelog

Artifacts are also available in the Actions tab for 90 days.

### 2. PHPUnit Tests (`phpunit.yml`)

**Trigger:** Manual dispatch only

Runs the PHPUnit test suite against a MySQL database.

### 3. Laravel Pint (`pint.yml`)

**Trigger:** Manual dispatch only

Runs Laravel Pint for code formatting checks.

### 4. PHPStan (`phpstan.yml`)

**Trigger:** Manual dispatch only

Runs PHPStan static analysis.

### 5. Docker Compose Check (`docker.yml`)

**Trigger:** Manual dispatch only

Tests Docker Compose configuration.

### 6. Quickstart (`quickstart.yml`)

**Trigger:** Manual dispatch only

Provides a quick setup for development environments.

## Workflow Optimization

### Vendor Directory Cleanup

The release workflow aggressively cleans the vendor directory to minimize file size:

- Removes all test directories (`tests`, `Tests`, `test`, `Test`)
- Removes all documentation (`docs`, `doc`, `*.md`, `*.txt`)
- Removes all Git metadata (`.git`, `.gitignore`, `.gitattributes`)
- Removes build files (`composer.json`, `composer.lock`, `phpunit.xml`, etc.)
- Removes code quality files (`.php_cs`, `phpstan.neon`, etc.)

This typically reduces the vendor directory size by 40-60%.

### ZIP Exclusions

The following files and directories are excluded from the release archive:

- Development files: `.github/*`, `tests/*`, `README.md`
- Configuration files: `phpunit.xml`, `phpstan.neon`, `pint.json`, `rector.php`
- Build tools: `package.json`, `yarn.lock`, `vite.config.js`, `tailwind.config.js`
- Docker files: `docker-compose.yml`
- Environment files: `.env*`
- Storage: `storage/logs/*`, `storage/framework/cache/*`
- Node modules: `node_modules/*` (already removed in cleanup step)

## Troubleshooting

### Crowdin Download Fails

If the Crowdin step fails, check:
1. Secrets are correctly configured
2. Your Crowdin personal token has not expired
3. The project ID is correct
4. Your Crowdin project is properly configured

### Build Fails

If the frontend build fails:
1. Ensure `package.json` is up to date
2. Check for syntax errors in Vite/Tailwind config
3. Verify all dependencies are correctly specified

### Composer Install Fails

If Composer installation fails:
1. Check `composer.json` for syntax errors
2. Ensure all required PHP extensions are available
3. Verify package versions are compatible

## Customization

### Changing PHP Version

Edit line 49 in `release.yml`:
```yaml
php-version: '8.3'  # Using 8.3 for latest features; composer.json requires ^8.2
```

### Changing Node.js Version

Edit line 36 in `release.yml`:
```yaml
node-version: '20'  # Change to your desired version
```

### Adjusting Artifact Retention

Edit line 121 in `release.yml`:
```yaml
retention-days: 90  # Change to your desired retention period (1-90 days)
```

### Custom ZIP Exclusions

Add or remove exclusions in the "Create release zip" step (lines 86-110).

## Best Practices

1. **Test locally first** - Before relying on the workflow, test the build process locally
2. **Monitor workflow runs** - Check the Actions tab regularly for failures
3. **Keep secrets secure** - Never commit secrets to the repository
4. **Update dependencies** - Keep GitHub Actions and dependencies up to date
5. **Tag releases** - Use semantic versioning for production releases

## Support

For issues or questions about these workflows:
- Create an issue in the repository
- Join the [Community Forums](https://community.invoiceplane.com)
- Visit the [Discord server](https://discord.gg/PPzD2hTrXt)
