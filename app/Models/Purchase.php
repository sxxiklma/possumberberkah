<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'invoice_number',
        'invoice_image',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'due_date',
        'status',
        'received_at',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'received_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

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
        return $this->hasMany(PurchaseItem::class);
    }

    public function payable()
    {
        return $this->hasOne(Payable::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference');
    }
}

