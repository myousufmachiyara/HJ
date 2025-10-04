<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'order_date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'vendor_id');
    }

    public function details()
    {
        return $this->hasMany(ProductionDetail::class);
    }

    public function receivings()
    {
        return $this->hasMany(ProductionReceiving::class);
    }

    public function productDetails()
    {
        return $this->hasMany(ProductionProductDetail::class);
    }
}

