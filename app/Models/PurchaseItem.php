<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'unit_price',
        'base_unit_cost',
        'discount_amount',
        'subtotal',
        'batch_number',
        'expiry_date',
        'warehouse_location_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'conversion_factor' => 'decimal:4',
        'base_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'base_unit_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
}

