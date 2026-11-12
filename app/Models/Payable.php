<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'due_date',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->remaining_balance > 0;
    }
}

