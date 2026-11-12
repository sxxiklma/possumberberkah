<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $fillable = [
        'sale_id',
        'customer_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->remaining_balance > 0;
    }
}

