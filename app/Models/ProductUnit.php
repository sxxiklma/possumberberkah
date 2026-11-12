<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'conversion_factor',
        'buy_price',
        'selling_price',
        'wholesale_price',
        'is_base',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:4',
        'buy_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'is_base' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

