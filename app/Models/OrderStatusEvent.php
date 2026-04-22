<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'status',
        'meta',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'meta' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
