<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemBatch extends Model
{
    protected $fillable = [
        'sale_item_id',
        'inventory_batch_id',
        'quantity_taken',
        'cost_price',
        'subtotal_cogs',
    ];

    protected $casts = [
        'quantity_taken' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal_cogs' => 'decimal:2',
    ];

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }
}

