<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'dispatcher_id',
        'origin',
        'destination',
        'planned_travel_details',
        'convoy_size',
        'start_date',
        'end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'convoy_size' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(Dispatcher::class);
    }
}
