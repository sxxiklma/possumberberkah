<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'type',
        'category',
        'amount',
        'transaction_date',
        'payment_method',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expense()
    {
        return $this->hasOne(Expense::class);
    }
}

