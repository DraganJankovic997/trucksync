<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStopBid extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SELECTED = 'selected';

    public const STATUS_REJECTED = 'rejected';

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'route_stop_bids';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

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
        'status',
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
            'status' => 'string',
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

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('route_stop_id', $this->getOriginal('route_stop_id') ?? $this->getAttribute('route_stop_id'))
            ->where('rest_stop_id', $this->getOriginal('rest_stop_id') ?? $this->getAttribute('rest_stop_id'));
    }
}
