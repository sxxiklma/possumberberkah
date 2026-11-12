<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'sale_date',
        'price_type',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'total_cogs',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_status',
        'due_date',
        'status',
        'notes',
        'void_by_user_id',
        'void_at',
        'void_reason',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'void_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voidByUser()
    {
        return $this->belongsTo(User::class, 'void_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function receivable()
    {
        return $this->hasOne(Receivable::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference');
    }

    public function getGrossProfitAttribute(): float
    {
        return (float) ($this->total_amount - $this->total_cogs);
    }
}

