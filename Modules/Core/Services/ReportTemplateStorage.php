<?php

namespace Modules\Core\Services;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\ReportBuilder\ReportBricksCollection;
use RuntimeException;

/**
 * Pure-file storage for report templates. No database tables — a template is
 * a folder holding manifest.json (metadata) and bands.json (layout data).
 *
 * Disk layout:
 *   system/{type}/{slug}/{manifest,bands}.json   — synced from resources/report-templates
 *   {company_id}/{slug}/{manifest,bands}.json    — company-owned clones
 *
 * Company paths are always derived from the authenticated tenant context,
 * never from caller-supplied ids, so one company can never address another
 * company's templates.
 */
class ReportTemplateStorage
{
    public const DISK = 'report_templates';

    public const SCOPE_SYSTEM = 'system';

    public const SCOPE_COMPANY = 'company';

    /**
     * List system templates, optionally filtered by document type.
     *
     * @return array<int, array{scope: string, type: string, slug: string, manifest: array}>
     */
    public function listSystem(?ReportTemplateType $type = null): array
    {
        $templates = [];
        $types     = $type ? [$type->value] : ReportTemplateType::values();

        foreach ($types as $typeValue) {
            foreach (Storage::disk(self::DISK)->directories(self::SCOPE_SYSTEM . '/' . $typeValue) as $directory) {
                $manifest = $this->readJson($directory . '/manifest.json');

                if ($manifest === null) {
                    continue;
                }

                $templates[] = [
                    'scope'    => self::SCOPE_SYSTEM,
                    'type'     => $typeValue,
                    'slug'     => basename($directory),
                    'manifest' => $manifest,
                ];
            }
        }

        return $templates;
    }

    /**
     * List the current company's templates, optionally filtered by type.
     *
     * @return array<int, array{scope: string, type: string, slug: string, manifest: array}>
     */
    public function listCompany(?ReportTemplateType $type = null): array
    {
        $templates = [];

        foreach (Storage::disk(self::DISK)->directories((string) $this->companyId()) as $directory) {
            $manifest = $this->readJson($directory . '/manifest.json');

            if ($manifest === null) {
                continue;
            }

            if ($type !== null && ($manifest['type'] ?? null) !== $type->value) {
                continue;
            }

            $templates[] = [
                'scope'    => self::SCOPE_COMPANY,
                'type'     => (string) ($manifest['type'] ?? ''),
                'slug'     => basename($directory),
                'manifest' => $manifest,
            ];
        }

        return $templates;
    }

    /**
     * Slug => display name options for template pickers. Company clones
     * shadow system templates that share a slug, matching the resolution
     * order used when rendering.
     *
     * @return array<string, string>
     */
    public function optionsForType(ReportTemplateType $type): array
    {
        $options = [];

        foreach ($this->listSystem($type) as $template) {
            $options[$template['slug']] = (string) ($template['manifest']['name'] ?? $template['slug']);
        }

        foreach ($this->listCompany($type) as $template) {
            $options[$template['slug']] = (string) ($template['manifest']['name'] ?? $template['slug']);
        }

        ksort($options);

        return $options;
    }

    public function exists(string $scope, string $slug, ?ReportTemplateType $type = null): bool
    {
        return Storage::disk(self::DISK)->exists($this->path($scope, $slug, $type) . '/manifest.json');
    }

    /**
     * Load a template. Bands are validated on load: unknown brick ids and
     * bricks not allowed in their band are skipped, widths fall back to
     * full, and configs are filtered against each brick's own schema.
     *
     * @return array{manifest: array, bands: array<string, array>}|null
     */
    public function load(string $scope, string $slug, ?ReportTemplateType $type = null): ?array
    {
        $base     = $this->path($scope, $slug, $type);
        $manifest = $this->readJson($base . '/manifest.json');

        if ($manifest === null) {
            return null;
        }

        $bands = $this->readJson($base . '/bands.json') ?? [];

        return [
            'manifest' => $manifest,
            'bands'    => $this->sanitizeBands($bands),
        ];
    }

    /**
     * Persist a template's manifest and bands. Bands are sanitized before
     * writing so only known bricks, valid widths, and schema-filtered
     * configs ever reach disk.
     */
    public function save(string $scope, string $slug, array $manifest, array $bands, ?ReportTemplateType $type = null): void
    {
        $base = $this->path($scope, $slug, $type);
        $disk = Storage::disk(self::DISK);

        $disk->put($base . '/manifest.json', $this->encodeJson($manifest));
        $disk->put($base . '/bands.json', $this->encodeJson($this->sanitizeBands($bands)));
    }

    /**
     * Clone a template into the current company (or into the system scope).
     * Cloning copies the folder and rewrites the manifest.
     *
     * @return array{scope: string, type: string, slug: string, manifest: array}
     */
    public function clone(
        string $fromScope,
        string $fromSlug,
        string $newName,
        ?ReportTemplateType $type = null,
        string $toScope = self::SCOPE_COMPANY,
    ): array {
        $source = $this->load($fromScope, $fromSlug, $type);

        if ($source === null) {
            throw new RuntimeException("Report template [{$fromScope}/{$fromSlug}] does not exist.");
        }

        $manifest     = $source['manifest'];
        $templateType = $type ?? ReportTemplateType::tryFrom((string) ($manifest['type'] ?? ''));
        $newSlug      = $this->uniqueSlug($toScope, Str::slug($newName), $templateType);

        $manifest['name']        = $newName;
        $manifest['slug']        = $newSlug;
        $manifest['cloned_from'] = $this->path($fromScope, $fromSlug, $type);

        $this->save($toScope, $newSlug, $manifest, $source['bands'], $templateType);

        return [
            'scope'    => $toScope,
            'type'     => (string) ($manifest['type'] ?? ''),
            'slug'     => $newSlug,
            'manifest' => $manifest,
        ];
    }

    /**
     * Rename a template's display name (the slug is stable once created).
     */
    public function rename(string $scope, string $slug, string $newName, ?ReportTemplateType $type = null): void
    {
        $template = $this->load($scope, $slug, $type);

        if ($template === null) {
            throw new RuntimeException("Report template [{$scope}/{$slug}] does not exist.");
        }

        $manifest         = $template['manifest'];
        $manifest['name'] = $newName;

        Storage::disk(self::DISK)->put(
            $this->path($scope, $slug, $type) . '/manifest.json',
            $this->encodeJson($manifest),
        );
    }

    /**
     * Delete a template folder. Shipped system defaults are protected.
     */
    public function delete(string $scope, string $slug, ?ReportTemplateType $type = null): bool
    {
        if ($scope === self::SCOPE_SYSTEM && $slug === 'default') {
            throw new RuntimeException('System default templates cannot be deleted.');
        }

        $base = $this->path($scope, $slug, $type);

        if ( ! Storage::disk(self::DISK)->exists($base . '/manifest.json')) {
            return false;
        }

        return Storage::disk(self::DISK)->deleteDirectory($base);
    }

    /**
     * Reduce arbitrary decoded band data to the valid five-band structure.
     *
     * @return array<string, array<int, array{brick: string, width: string, config: array}>>
     */
    public function sanitizeBands(array $bands): array
    {
        $sanitized = [];

        foreach (ReportBand::ordered() as $band) {
            $sanitized[$band->value] = [];

            $entries = $bands[$band->value] ?? [];

            if ( ! is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                if ( ! is_array($entry)) {
                    continue;
                }

                $brickClass = ReportBricksCollection::findById((string) ($entry['brick'] ?? ''));

                if ($brickClass === null || ! in_array($band, $brickClass::allowedBands(), true)) {
                    continue;
                }

                $width  = ReportBlockWidth::tryFrom((string) ($entry['width'] ?? '')) ?? ReportBlockWidth::FULL;
                $config = is_array($entry['config'] ?? null) ? $entry['config'] : [];

                $sanitized[$band->value][] = [
                    'brick'  => $brickClass::getId(),
                    'width'  => $width->value,
                    'config' => $brickClass::filterConfig($config),
                ];
            }
        }

        return $sanitized;
    }

    /**
     * Resolve the disk path for a template. Slugs are strictly validated
     * ([a-z0-9-] only) so path traversal is impossible; company paths come
     * from the tenant context exclusively.
     */
    public function path(string $scope, string $slug, ?ReportTemplateType $type = null): string
    {
        $this->assertValidSlug($slug);

        if ($scope === self::SCOPE_SYSTEM) {
            if ($type === null) {
                throw new InvalidArgumentException('System template paths require a document type.');
            }

            return self::SCOPE_SYSTEM . '/' . $type->value . '/' . $slug;
        }

        if ($scope === self::SCOPE_COMPANY) {
            return $this->companyId() . '/' . $slug;
        }

        throw new InvalidArgumentException("Unknown report template scope [{$scope}].");
    }

    protected function assertValidSlug(string $slug): void
    {
        if ($slug === '' || preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug) !== 1) {
            throw new InvalidArgumentException("Invalid report template slug [{$slug}].");
        }
    }

    /**
     * Current company id from the tenant context (Filament tenant, then
     * session, then the user's first company). Never caller-supplied.
     */
    protected function companyId(): int
    {
        $tenant = Filament::getTenant();

        if ($tenant !== null) {
            return (int) $tenant->getKey();
        }

        if (session()?->has('current_company_id')) {
            return (int) session('current_company_id');
        }

        $company = Auth::user()?->companies()->first();

        if ($company !== null) {
            return (int) $company->id;
        }

        throw new RuntimeException('No company context available for report template storage.');
    }

    protected function uniqueSlug(string $scope, string $slug, ?ReportTemplateType $type): string
    {
        $this->assertValidSlug($slug);

        $candidate = $slug;
        $suffix    = 2;

        while ($this->exists($scope, $candidate, $type)) {
            $candidate = $slug . '-' . $suffix++;
        }

        return $candidate;
    }

    protected function readJson(string $path): ?array
    {
        if ( ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        try {
            $decoded = json_decode((string) Storage::disk(self::DISK)->get($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    protected function encodeJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
