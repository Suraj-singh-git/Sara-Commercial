<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'contact_name',
        'phone',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
