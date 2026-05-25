<?php

namespace Modules\Core\Services\Import;

use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

class ProductsImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_families', 'ip_units', 'ip_products'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['product_categories', 'product_units', 'products']);

        $this->importProductCategories();
        $this->importProductUnits();
        $this->importProducts();

        return $this->stats;
    }

    private function importProductCategories(): void
    {
        $families = $this->getImportData('ip_families');

        foreach ($families as $family) {
            $category = ProductCategory::create([
                'company_id'    => $this->companyId,
                'category_name' => $family->family_name,
                'description'   => null,
            ]);

            $this->idMappings['product_families'][$family->family_id] = $category->id;
            $this->stats['product_categories']++;
        }
    }

    private function importProductUnits(): void
    {
        $units = $this->getImportData('ip_units');

        foreach ($units as $unit) {
            $productUnit = ProductUnit::create([
                'company_id'     => $this->companyId,
                'unit_name'      => $unit->unit_name,
                'unit_name_plrl' => $unit->unit_name_plrl ?? $unit->unit_name,
            ]);

            $this->idMappings['product_units'][$unit->unit_id] = $productUnit->id;
            $this->stats['product_units']++;
        }
    }

    private function importProducts(): void
    {
        $products = $this->getImportData('ip_products');

        foreach ($products as $v1Product) {
            $categoryId = $this->idMappings['product_families'][$v1Product->family_id] ?? null;
            $unitId = $this->idMappings['product_units'][$v1Product->unit_id] ?? null;
            $taxRateId = $this->idMappings['tax_rates'][$v1Product->tax_rate_id] ?? null;

            if (! $categoryId) {
                $defaultCategory = ProductCategory::firstOrCreate([
                    'company_id'    => $this->companyId,
                    'category_name' => 'Default',
                    'description'   => 'Default category for imported products',
                ]);
                $categoryId = $defaultCategory->id;
            }

            $product = Product::create([
                'company_id'   => $this->companyId,
                'category_id'  => $categoryId,
                'unit_id'      => $unitId,
                'type'         => 'service',
                'code'         => $v1Product->product_sku ?? null,
                'product_name' => $v1Product->product_name,
                'price'        => $v1Product->product_price ?? 0,
                'tax_rate_id'  => $taxRateId,
                'description'  => $v1Product->product_description ?? null,
            ]);

            $this->idMappings['products'][$v1Product->product_id] = $product->id;
            $this->stats['products']++;
        }
    }
}
