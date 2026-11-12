<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'inventory_batch_id',
        'type',
        'reference_number',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

