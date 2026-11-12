<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    protected $fillable = [
        'warehouse_id',
        'code',
        'area',
        'rack',
        'row',
        'column',
        'notes',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

