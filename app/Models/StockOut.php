<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'reference_number',
        'date',
        'reason',
        'warehouse_id',
        'user_id',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total_cost' => 'decimal:2',
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
        return $this->hasMany(StockOutItem::class);
    }
}

