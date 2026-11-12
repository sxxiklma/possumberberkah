<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number',
        'opname_date',
        'warehouse_id',
        'user_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}

