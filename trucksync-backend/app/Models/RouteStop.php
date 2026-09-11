<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteStop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'route_id',
        'stop_at',
        'fulfiled_at',
        'fulfiled_by',
        'number_of_trucks',
        'number_of_drivers',
        'location',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'route_id' => 'integer',
            'stop_at' => 'datetime',
            'fulfiled_at' => 'datetime',
            'fulfiled_by' => 'integer',
            'number_of_trucks' => 'integer',
            'number_of_drivers' => 'integer',
            'bids_count' => 'integer',
            'accepted_bid_price' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithAcceptedBidPrice(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        if ($query->getQuery()->columns === null) {
            $query->select("{$table}.*");
        }

        return $query->addSelect([
            'accepted_bid_price' => RouteStopBid::query()
                ->select('price')
                ->whereColumn('route_stop_bids.route_stop_id', "{$table}.id")
                ->whereColumn('route_stop_bids.rest_stop_id', "{$table}.fulfiled_by")
                ->limit(1),
        ]);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function fulfiledBy(): BelongsTo
    {
        return $this->belongsTo(RestStop::class, 'fulfiled_by');
    }

    public function routeStopServices(): HasMany
    {
        return $this->hasMany(RouteStopService::class);
    }

    public function routeStopBids(): HasMany
    {
        return $this->hasMany(RouteStopBid::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'route_stop_services')
            ->withPivot('quantity');
    }
}
