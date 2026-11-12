<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductUnit;

class UnitConversionService
{
    /**
     * Convert given quantity in a specific unit to base unit quantity.
     */
    public function convertToBase(Product $product, int $unitId, float $quantity): float
    {
        if ($unitId == $product->base_unit_id) {
            return $quantity;
        }

        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->first();

        if ($productUnit && $productUnit->conversion_factor > 0) {
            return round($quantity * (float) $productUnit->conversion_factor, 4);
        }

        return $quantity;
    }

    /**
     * Get conversion factor for a unit relative to product's base unit.
     */
    public function getConversionFactor(Product $product, int $unitId): float
    {
        if ($unitId == $product->base_unit_id) {
            return 1.0;
        }

        $productUnit = ProductUnit::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->first();

        return $productUnit ? (float) $productUnit->conversion_factor : 1.0;
    }

    /**
     * Format a base unit quantity into a composite multi-unit string.
     * Example: 975 PCS with Pack (100) -> "9 PACK + 75 PCS"
     */
    public function formatStock(Product $product, ?float $quantity = null): string
    {
        return self::formatCompositeUnits($product, $quantity);
    }

    public static function formatCompositeUnits(Product $product, ?float $quantity = null): string
    {
        $current = $quantity ?? $product->current_stock;
        $baseUnit = $product->baseUnit ?: Unit::find($product->base_unit_id);
        $baseCode = $baseUnit ? $baseUnit->code : 'PCS';

        $largerUnit = ProductUnit::with('unit')
            ->where('product_id', $product->id)
            ->where('conversion_factor', '>', 1)
            ->orderBy('conversion_factor', 'desc')
            ->first();

        if ($largerUnit && $largerUnit->conversion_factor > 1 && $current >= $largerUnit->conversion_factor) {
            $packFactor = (float) $largerUnit->conversion_factor;
            $packCount = floor($current / $packFactor);
            $remainder = fmod($current, $packFactor);
            $packCode = $largerUnit->unit ? $largerUnit->unit->code : 'PACK';

            if ($remainder > 0) {
                return "{$packCount} {$packCode} + " . (float)$remainder . " {$baseCode}";
            }
            return "{$packCount} {$packCode}";
        }

        return (float)$current . " {$baseCode}";
    }

    /**
     * Get all usable units with prices and conversion factors for POS and Purchase item pickers.
     */
    public function getProductUnitsWithPricing(Product $product): array
    {
        $product->loadMissing(['baseUnit', 'productUnits.unit']);
        $units = [];

        $baseBuy = (float) $product->buy_price;
        $baseSell = (float) $product->selling_price;
        $baseWs = (float) ($product->wholesale_price ?: $product->selling_price);

        // Base unit
        $units[] = [
            'unit_id' => $product->base_unit_id,
            'unit_code' => $product->baseUnit?->code ?? 'PCS',
            'unit_name' => $product->baseUnit?->name ?? 'Pcs',
            'conversion_factor' => 1.0,
            'is_base' => true,
            'buy_price' => $baseBuy,
            'selling_price' => $baseSell,
            'wholesale_price' => $baseWs,
        ];

        // Secondary / packaging units
        foreach ($product->productUnits as $pu) {
            if ($pu->unit_id == $product->base_unit_id) {
                continue;
            }

            $factor = (float) ($pu->conversion_factor > 0 ? $pu->conversion_factor : 1);
            $puBuy = (float) ($pu->buy_price ?? 0);
            $puSell = (float) ($pu->selling_price ?? 0);
            $puWs = (float) ($pu->wholesale_price ?? 0);

            // Default calculated package price if not explicitly set
            $calcBuy = round($baseBuy * $factor, 2);
            $calcSell = round($baseSell * $factor, 2);
            $calcWs = round($baseWs * $factor, 2);

            // If package price is 0, missing, or mistakenly set <= base unit price when factor > 1
            $finalBuy = ($puBuy > 0 && ($factor <= 1 || $puBuy > $baseBuy)) ? $puBuy : $calcBuy;
            $finalSell = ($puSell > 0 && ($factor <= 1 || $puSell > $baseSell)) ? $puSell : $calcSell;
            $finalWs = ($puWs > 0 && ($factor <= 1 || $puWs > $baseWs)) ? $puWs : $calcWs;

            $units[] = [
                'unit_id' => $pu->unit_id,
                'unit_code' => $pu->unit?->code ?? 'UNIT',
                'unit_name' => $pu->unit?->name ?? 'Unit',
                'conversion_factor' => $factor,
                'is_base' => false,
                'buy_price' => $finalBuy,
                'selling_price' => $finalSell,
                'wholesale_price' => $finalWs,
            ];
        }

        return $units;
    }
}
