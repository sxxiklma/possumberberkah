<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }
}

