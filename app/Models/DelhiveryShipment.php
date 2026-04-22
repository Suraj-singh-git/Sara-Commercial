<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DelhiveryShipment extends Model
{
    protected $fillable = [
        'order_id',
        'waybill',
        'status',
        'last_payload',
    ];

    protected function casts(): array
    {
        return [
            'last_payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
