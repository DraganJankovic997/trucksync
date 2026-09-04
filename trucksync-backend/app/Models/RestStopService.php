<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestStopService extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'rest_stop_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rest_stop_id',
        'service_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rest_stop_id' => 'integer',
            'service_id' => 'integer',
        ];
    }

    public function restStop(): BelongsTo
    {
        return $this->belongsTo(RestStop::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
