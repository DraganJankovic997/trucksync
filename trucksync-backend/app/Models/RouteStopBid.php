<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStopBid extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'route_stop_bids';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'route_stop_id',
        'rest_stop_id',
        'price',
        'original_price',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'route_stop_id' => 'integer',
            'rest_stop_id' => 'integer',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
        ];
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function restStop(): BelongsTo
    {
        return $this->belongsTo(RestStop::class);
    }
}
