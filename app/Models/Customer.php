<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'category',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

