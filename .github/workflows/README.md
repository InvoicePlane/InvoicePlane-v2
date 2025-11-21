# GitHub Actions Workflows

This directory contains GitHub Actions workflows for automated CI/CD tasks.

## Available Workflows

### 1. Production Release (`release.yml`)

**Trigger:** Automatically runs on every push to the `master` branch

**Purpose:** Creates a production-ready release package of InvoicePlane v2

**What it does:**
1. **Downloads translations from Crowdin** - Retrieves the latest translations
2. **Builds frontend assets** - Runs `yarn install --frozen-lockfile && yarn build`
3. **Installs PHP dependencies** - Runs `composer install --no-dev` for production
4. **Cleans up node_modules** - Removes Node.js dependencies
5. **Optimizes vendor directory** - Removes unnecessary files (tests, docs, etc.)
6. **Creates release archive** - Packages everything into a timestamped ZIP file
7. **Uploads artifact** - Makes the release available for download (90-day retention)

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

**Accessing Release Artifacts:**

After the workflow runs:
1. Go to the Actions tab in your repository
2. Click on the completed "Build Production Release" workflow run
3. Scroll down to the "Artifacts" section
4. Download the ZIP file (named `invoiceplane-v2-YYYYMMDD_HHMMSS.zip`)

**Optional: Automatic GitHub Releases**

The workflow includes commented-out code for creating GitHub releases.
To enable automatic releases when you create a tag:

1. Uncomment lines 140-146 in `release.yml`
2. Create and push a tag:
   ```bash
   git tag v2.0.0
   git push origin v2.0.0
   ```
3. The workflow will create a GitHub Release with the ZIP file attached

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
php-version: '8.3'  # Change to your desired version
```

### Changing Node.js Version

Edit line 36 in `release.yml`:
```yaml
node-version: '20'  # Change to your desired version
```

### Adjusting Artifact Retention

Edit line 137 in `release.yml`:
```yaml
retention-days: 90  # Change to your desired retention period (1-90 days)
```

### Custom ZIP Exclusions

Add or remove exclusions in the "Create release zip" step (lines 102-126).

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
