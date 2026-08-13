<?php

namespace Modules\Core\Services\Migration\Migrators;

use Modules\Core\Services\Migration\Contracts\EntityMigratorInterface;
use Modules\Core\Services\Migration\MigrationContext;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

class ProductMigrator implements EntityMigratorInterface
{
    public function name(): string
    {
        return 'products';
    }

    public function label(): string
    {
        return 'Products & Categories';
    }

    public function inspect(MigrationContext $context): array
    {
        $families = $context->getSourceTable('families');
        $units = $context->getSourceTable('units');
        $products = $context->getSourceTable('products');

        $notes = [];
        $willMigrate = 0;
        $unmappable = 0;

        foreach ($products as $row) {
            $name = trim((string) ($row['product_name'] ?? ''));
            if ($name === '') {
                $unmappable++;
                $notes[] = "Product row #{$row['product_id']} has empty name, will be skipped.";
            } else {
                $willMigrate++;
            }
        }

        return [
            'source_count' => $families->count() + $units->count() + $products->count(),
            'will_migrate' => $families->count() + $units->count() + $willMigrate,
            'unmappable'   => $unmappable,
            'notes'        => $notes,
        ];
    }

    public function migrate(MigrationContext $context): array
    {
        $migrated = 0;
        $skipped = 0;
        $errors = [];

        // 1. Families -> ProductCategory
        $families = $context->getSourceTable('families');
        foreach ($families as $row) {
            $v1Id = $row['family_id'] ?? null;
            $name = trim((string) ($row['family_name'] ?? ''));
            if ($name === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('product_categories', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $category = ProductCategory::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('category_name', $name)
                    ->first();

                if (!$category) {
                    $category = ProductCategory::create([
                        'company_id'    => $context->getCompanyId(),
                        'category_name' => $name,
                    ]);
                    $context->recordCreated(ProductCategory::class, $category->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('product_categories', $v1Id, $category->id);
                }
                $migrated++;
            } catch (\Throwable $e) {
                $errors[] = "Failed to migrate product category '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        // 2. Units -> ProductUnit
        $units = $context->getSourceTable('units');
        foreach ($units as $row) {
            $v1Id = $row['unit_id'] ?? null;
            $name = trim((string) ($row['unit_name'] ?? ''));
            $namePlural = trim((string) ($row['unit_name_plrl'] ?? ''));
            if ($name === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('product_units', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $unit = ProductUnit::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('unit_name', $name)
                    ->first();

                if (!$unit) {
                    $unit = ProductUnit::create([
                        'company_id'     => $context->getCompanyId(),
                        'unit_name'      => $name,
                        'unit_name_plrl' => $namePlural ?: $name,
                    ]);
                    $context->recordCreated(ProductUnit::class, $unit->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('product_units', $v1Id, $unit->id);
                }
                $migrated++;
            } catch (\Throwable $e) {
                $errors[] = "Failed to migrate product unit '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        // 3. Products -> Product
        $products = $context->getSourceTable('products');
        foreach ($products as $row) {
            $v1Id = $row['product_id'] ?? null;
            $name = trim((string) ($row['product_name'] ?? ''));
            if ($name === '') {
                $skipped++;
                continue;
            }

            if ($context->isDryRun()) {
                if ($v1Id !== null) {
                    $context->mapId('products', $v1Id, (int) $v1Id);
                }
                $migrated++;
                continue;
            }

            try {
                $categoryId = $context->getId('product_categories', $row['family_id'] ?? null);
                if (!$categoryId) {
                    // Get or create a default category
                    $defaultCategory = ProductCategory::withoutGlobalScopes()
                        ->where('company_id', $context->getCompanyId())
                        ->first();

                    if (!$defaultCategory) {
                        $defaultCategory = ProductCategory::create([
                            'company_id'    => $context->getCompanyId(),
                            'category_name' => 'General',
                        ]);
                        $context->recordCreated(ProductCategory::class, $defaultCategory->id);
                    }
                    $categoryId = $defaultCategory->id;
                }

                $unitId = $context->getId('product_units', $row['unit_id'] ?? null);
                $taxRateId = $context->getId('tax_rates', $row['tax_rate_id'] ?? null);

                $product = Product::withoutGlobalScopes()
                    ->where('company_id', $context->getCompanyId())
                    ->where('product_name', $name)
                    ->first();

                if (!$product) {
                    $product = Product::create([
                        'company_id'     => $context->getCompanyId(),
                        'category_id'    => $categoryId,
                        'unit_id'        => $unitId,
                        'tax_rate_id'    => $taxRateId,
                        'type'           => ProductType::PRODUCT,
                        'code'           => !empty($row['product_sku']) ? (string) $row['product_sku'] : null,
                        'product_name'   => $name,
                        'description'    => !empty($row['product_description']) ? (string) $row['product_description'] : null,
                        'price'          => (float) ($row['product_price'] ?? 0.0),
                        'cost_price'     => (float) ($row['purchase_price'] ?? 0.0),
                        'product_tariff' => !empty($row['product_tariff']) ? (int) $row['product_tariff'] : null,
                    ]);
                    $context->recordCreated(Product::class, $product->id);
                }

                if ($v1Id !== null) {
                    $context->mapId('products', $v1Id, $product->id);
                }
                $migrated++;
            } catch (\Throwable $e) {
                $errors[] = "Failed to migrate product #{$v1Id} '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        $context->log("Migrated {$migrated} product items ({$skipped} skipped).");

        return [
            'migrated' => $migrated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    public function rollback(MigrationContext $context): int
    {
        $productIds = $context->getCreatedIds(Product::class);
        $categoryIds = $context->getCreatedIds(ProductCategory::class);
        $unitIds = $context->getCreatedIds(ProductUnit::class);

        $deleted = 0;
        if (!empty($productIds)) {
            $deleted += Product::withoutGlobalScopes()->whereIn('id', $productIds)->delete();
        }
        if (!empty($categoryIds)) {
            $deleted += ProductCategory::withoutGlobalScopes()->whereIn('id', $categoryIds)->delete();
        }
        if (!empty($unitIds)) {
            $deleted += ProductUnit::withoutGlobalScopes()->whereIn('id', $unitIds)->delete();
        }

        return $deleted;
    }
}
