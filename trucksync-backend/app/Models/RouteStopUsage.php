<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStopUsage extends Model
{
    use HasFactory;

    public const MIN_RATING = 1;

    public const MAX_RATING = 5;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_report' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'route_stop_id',
        'driver_id',
        'rest_stop_id',
        'used_at',
        'rating',
        'report',
        'is_report',
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
            'driver_id' => 'integer',
            'rest_stop_id' => 'integer',
            'used_at' => 'datetime',
            'rating' => 'integer',
            'is_report' => 'boolean',
        ];
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function restStop(): BelongsTo
    {
        return $this->belongsTo(RestStop::class);
    }
}
