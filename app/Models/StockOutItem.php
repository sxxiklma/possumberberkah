<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOutItem extends Model
{
    protected $fillable = [
        'stock_out_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_factor',
        'base_quantity',
        'cogs_total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'conversion_factor' => 'decimal:4',
        'base_quantity' => 'decimal:2',
        'cogs_total' => 'decimal:2',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

