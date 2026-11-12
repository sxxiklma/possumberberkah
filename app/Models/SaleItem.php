<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'unit_price',
        'discount_amount',
        'subtotal',
        'cogs_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'conversion_factor' => 'decimal:4',
        'base_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cogs_total' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches()
    {
        return $this->hasMany(SaleItemBatch::class);
    }
}

