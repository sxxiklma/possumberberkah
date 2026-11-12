<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}

