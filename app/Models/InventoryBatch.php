<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'product_id',
        'warehouse_id',
        'warehouse_location_id',
        'purchase_item_id',
        'initial_quantity',
        'available_quantity',
        'cost_price',
        'received_date',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'initial_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'received_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function saleItemBatches()
    {
        return $this->hasMany(SaleItemBatch::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}

