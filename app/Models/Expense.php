<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'cash_transaction_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashTransaction()
    {
        return $this->belongsTo(CashTransaction::class);
    }
}

