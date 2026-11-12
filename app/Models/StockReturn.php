<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'type',
        'sale_id',
        'purchase_id',
        'customer_id',
        'supplier_id',
        'return_date',
        'total_amount',
        'reason',
        'status',
        'user_id',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}

