<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'measurement_unit',
    ];

    public function restStopServices(): HasMany
    {
        return $this->hasMany(RestStopService::class);
    }

    public function routeStopServices(): HasMany
    {
        return $this->hasMany(RouteStopService::class);
    }

    public function restStops(): BelongsToMany
    {
        return $this->belongsToMany(RestStop::class, 'rest_stop_services');
    }

    public function routeStops(): BelongsToMany
    {
        return $this->belongsToMany(RouteStop::class, 'route_stop_services')
            ->withPivot('quantity');
    }
}
