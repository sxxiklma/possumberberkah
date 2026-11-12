<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'slug',
        'image',
        'category_id',
        'brand_id',
        'description',
        'base_unit_id',
        'purchase_unit_id',
        'selling_unit_id',
        'buy_price',
        'selling_price',
        'wholesale_price',
        'min_stock',
        'weight',
        'warehouse_location_id',
        'has_expiry',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'buy_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'weight' => 'decimal:2',
        'has_expiry' => 'boolean',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'current_stock',
        'stock_formatted',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function sellingUnit()
    {
        return $this->belongsTo(Unit::class, 'selling_unit_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function activeBatches()
    {
        return $this->hasMany(InventoryBatch::class)
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->orderBy('received_date', 'asc');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getCurrentStockAttribute(): float
    {
        return (float) $this->batches()
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');
    }

    public function getInventoryValueAttribute(): float
    {
        return (float) $this->batches()
            ->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->selectRaw('SUM(available_quantity * cost_price) as val')
            ->value('val') ?? 0;
    }

    /**
     * Format multi-unit stock display.
     * Example: 240 PCS with BOX (12) -> "20 BOX"
     * Example: 245 PCS with BOX (12) -> "20 BOX + 5 PCS"
     */
    public function getStockFormattedAttribute(): string
    {
        $current = (float) $this->current_stock;
        $baseCode = $this->baseUnit ? $this->baseUnit->code : 'PCS';

        if ($current <= 0) {
            return "0 {$baseCode}";
        }

        $largerUnits = $this->productUnits()
            ->with('unit')
            ->where('conversion_factor', '>', 1)
            ->orderBy('conversion_factor', 'desc')
            ->get();

        if ($largerUnits->isEmpty()) {
            return "{$current} {$baseCode}";
        }

        $remaining = $current;
        $parts = [];

        foreach ($largerUnits as $lu) {
            $factor = (float) $lu->conversion_factor;
            if ($factor > 1 && $remaining >= $factor) {
                $count = floor($remaining / $factor);
                $remaining = fmod($remaining, $factor);
                $parts[] = "{$count} " . ($lu->unit?->code ?? 'UNIT');
            }
        }

        if ($remaining > 0 || empty($parts)) {
            $parts[] = (float)$remaining . " {$baseCode}";
        }

        return implode(' + ', $parts);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('status', 'active');
        }, '<=', function () {
            // or subquery sum
        });
    }
}

