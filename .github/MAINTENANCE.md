# Maintenance Guide for InvoicePlane v2

This document provides guidelines for maintaining the InvoicePlane v2 application, including dependency management, security updates, and best practices.

---

## Dependency Management

### Package Managers

InvoicePlane v2 uses two package managers:

- **Composer** - PHP dependencies (backend)
- **Yarn** - JavaScript dependencies (frontend)

### Lockfiles

Both package managers use lockfiles to ensure consistent dependency versions:

- `composer.lock` - Locks PHP dependencies
- `yarn.lock` - Locks JavaScript dependencies

---

## When to Use `--frozen-lockfile`

### Composer

Use `composer install --no-interaction --prefer-dist` in the following scenarios:

- **CI/CD Pipelines** - To ensure reproducible builds
- **Production Deployments** - To install exact versions from lockfile
- **Testing Environments** - To test against known dependency versions

### Yarn

Use `yarn install --frozen-lockfile` in the following scenarios:

- **CI/CD Pipelines** - To ensure consistent builds across environments
- **Production Deployments** - To prevent unexpected dependency changes
- **Team Collaboration** - To ensure all developers use the same versions

**Example GitHub Actions:**
```yaml
- name: Install Composer dependencies
 run: composer install --no-interaction --prefer-dist --optimize-autoloader

- name: Install Yarn dependencies
 run: yarn install --frozen-lockfile
```

---

## When to "Unfreeze" and Upgrade Packages

### Regular Maintenance

Perform dependency updates in the following scenarios:

1. **Security Updates** - Immediately when security vulnerabilities are discovered
2. **Monthly Updates** - Scheduled maintenance for minor and patch updates
3. **Major Updates** - Quarterly or as needed for major version updates
4. **Feature Requirements** - When new features require updated dependencies

### How to Upgrade

#### Composer (PHP Dependencies)

```bash
# Update all dependencies (respecting version constraints)
composer update

# Update specific package
composer update vendor/package

# Update with security fixes only
composer update --with-dependencies

# Dry run to see what would be updated
composer update --dry-run
```

#### Yarn (JavaScript Dependencies)

```bash
# Update all dependencies (respecting version constraints)
yarn upgrade

# Update specific package
yarn upgrade package-name

# Update to latest versions (ignore constraints)
yarn upgrade-interactive --latest

# Check for outdated packages
yarn outdated
```

### Before Upgrading

1. **Review Changelog** - Read release notes and breaking changes
2. **Backup** - Create a backup or work in a separate branch
3. **Test Locally** - Run full test suite after upgrades
4. **Update Gradually** - Update one package at a time for major versions

### After Upgrading

1. **Run Tests** - Execute full test suite to ensure compatibility
2. **Update Code** - Fix any breaking changes or deprecations
3. **Update Documentation** - Document any significant dependency changes
4. **Commit Lockfiles** - Always commit updated lockfiles

---

## Security Alert Response Process

### When You Receive a Security Alert

GitHub Dependabot and other tools will notify you of security vulnerabilities. Follow this process to respond quickly and effectively:

#### 1. **Assess the Alert**

- **Review the CVE** - Understand the vulnerability and its impact
- **Check Severity** - Critical and High severity alerts require immediate action
- **Determine Scope** - Identify affected parts of the application
- **Check Exploitability** - Is the vulnerability actively exploited?

#### 2. **Prioritize Response**

| Severity | Response Time | Action |
|----------|---------------|--------|
| **Critical** | Immediate (within 24 hours) | Emergency patch and deploy |
| **High** | 1-3 days | Patch and deploy quickly |
| **Medium** | 1-2 weeks | Include in next maintenance cycle |
| **Low** | 1 month | Include in monthly update |

#### 3. **Apply the Fix**

```bash
# For Composer dependencies
composer update vendor/package --with-dependencies

# For Yarn dependencies
yarn upgrade package-name

# Run tests to verify the fix
php artisan test
```

#### 4. **Verify the Fix**

- Run the full test suite
- Test affected functionality manually
- Use security scanning tools to verify the fix:
 ```bash
 composer audit
 yarn audit
 ```

#### 5. **Deploy**

- **Critical/High Severity** - Deploy as a hotfix
- **Medium/Low Severity** - Include in regular deployment cycle

#### 6. **Document**

- Update `CHANGELOG.md` with security fix details
- Create a security advisory if necessary
- Notify users if the vulnerability affected production

---

## Automated Dependency Scanning

### GitHub Dependabot

InvoicePlane v2 uses GitHub Dependabot to automatically detect and create pull requests for security updates.

**Dependabot Configuration** (`.github/dependabot.yml`):
```yaml
version: 2
updates:
 # Composer
 - package-ecosystem: "composer"
 directory: "/"
 schedule:
 interval: "weekly"
 open-pull-requests-limit: 10

 # npm/Yarn
 - package-ecosystem: "npm"
 directory: "/"
 schedule:
 interval: "weekly"
 open-pull-requests-limit: 10
```

### Manual Security Audits

Run periodic security audits manually:

```bash
# Composer security audit
composer audit

# Yarn security audit
yarn audit

# Fix Yarn vulnerabilities automatically (when possible)
yarn audit --fix
```

---

## Maintenance Schedule

### Weekly

- Review Dependabot pull requests
- Check for critical security alerts
- Monitor error logs for issues

### Monthly

- Update dependencies (patch and minor versions)
- Review and merge dependency updates
- Run full test suite
- Update documentation if needed

### Quarterly

- Review major version updates
- Plan and test major upgrades
- Update infrastructure dependencies
- Comprehensive security audit

### Annually

- Review and update maintenance processes
- Evaluate new tools and practices
- Major refactoring and technical debt reduction

---

## GitHub Actions Workflows

### Automated Dependency Updates

InvoicePlane v2 includes GitHub Actions workflows for automated dependency management:

- **Composer Update Workflow** - `.github/workflows/composer-update.yml`
- **Yarn Update Workflow** - `.github/workflows/yarn-update.yml`

These workflows can be triggered manually or on a schedule to:
- Update dependencies
- Run tests
- Create pull requests with updates

### Crowdin Translation Sync

InvoicePlane v2 includes a GitHub Actions workflow for automated translation management:

- **Crowdin Sync Workflow** - `.github/workflows/crowdin-sync.yml`

This workflow can be triggered manually with three action types:

1. **upload-sources** - Upload source translation files to Crowdin
2. **download-translations** - Download translated files from Crowdin (default)
3. **sync-bidirectional** - Upload sources and download translations

The workflow runs automatically on a weekly schedule (Sundays at 2:00 AM UTC) to download new translations and create pull requests.

**Required Secrets:**

To configure GitHub secrets for the Crowdin workflow:

1. Go to your repository on GitHub
2. Navigate to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Add the following secrets:
   - `CROWDIN_PROJECT_ID` - Your Crowdin project ID
   - `CROWDIN_PERSONAL_TOKEN` - Your Crowdin personal access token

Direct URL: `https://github.com/InvoicePlane/InvoicePlane-v2/settings/secrets/actions`

**Manual Trigger:**
```bash
# Go to Actions tab → Crowdin Translation Sync → Run workflow
# Select desired action type
```

See: `.github/workflows/` directory for workflow details.

---

## Tools and Commands

### Code Quality

```bash
# Format code
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse

# Rector (automated refactoring)
vendor/bin/rector process --dry-run
```

### Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Unit
```

### Database

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback and migrate
php artisan migrate:refresh
```

---

## Best Practices

### General

1. **Always commit lockfiles** - `composer.lock` and `yarn.lock`
2. **Test before deploying** - Run full test suite after updates
3. **Use branches** - Create a branch for dependency updates
4. **Document changes** - Update CHANGELOG.md
5. **Review pull requests** - Don't auto-merge dependency updates

### Security

1. **Act quickly on critical alerts** - Prioritize security over features
2. **Subscribe to security mailing lists** - Stay informed about vulnerabilities
3. **Use security headers** - Implement proper security headers in production
4. **Regular backups** - Maintain regular database and file backups

### Dependencies

1. **Keep dependencies up to date** - Regular updates reduce security risks
2. **Minimize dependencies** - Only add necessary packages
3. **Review new dependencies** - Check package reputation and maintenance
4. **Use semantic versioning** - Understand version constraints in composer.json/package.json

---

## Additional Resources

- **Installation Guide** - `.github/INSTALLATION.md`
- **Contributing Guide** - `.github/CONTRIBUTING.md`
- **Security Policy** - `.github/SECURITY.md`
- **Upgrade Guide** - `.github/UPGRADE.md`
- **Composer Documentation** - https://getcomposer.org/doc/
- **Yarn Documentation** - https://yarnpkg.com/getting-started
- **GitHub Dependabot** - https://docs.github.com/en/code-security/dependabot

---

## Support

If you encounter issues with dependency management or security updates:

- **Discord** - https://discord.gg/PPzD2hTrXt
- **Forums** - https://community.invoiceplane.com
- **GitHub Issues** - https://github.com/InvoicePlane/InvoicePlane-v2/issues
- **Security Issues** - See `.github/SECURITY.md` for responsible disclosure

---

**Last Updated:** 2025-12-29
