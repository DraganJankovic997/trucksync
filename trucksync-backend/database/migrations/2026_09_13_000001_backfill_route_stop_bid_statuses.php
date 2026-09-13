<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATUS_PENDING = 'pending';

    private const STATUS_SELECTED = 'selected';

    private const STATUS_REJECTED = 'rejected';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('route_stop_bids')
            ->join('route_stops', 'route_stop_bids.route_stop_id', '=', 'route_stops.id')
            ->join('routes', 'route_stops.route_id', '=', 'routes.id')
            ->select([
                'route_stop_bids.route_stop_id',
                'route_stop_bids.rest_stop_id',
                'route_stops.fulfilled_by',
                'routes.closed_at',
            ])
            ->orderBy('route_stop_bids.route_stop_id')
            ->orderBy('route_stop_bids.rest_stop_id')
            ->each(function (object $bid): void {
                DB::table('route_stop_bids')
                    ->where('route_stop_id', $bid->route_stop_id)
                    ->where('rest_stop_id', $bid->rest_stop_id)
                    ->update([
                        'status' => $this->statusForBid($bid),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('route_stop_bids')->update([
            'status' => self::STATUS_PENDING,
        ]);
    }

    private function statusForBid(object $bid): string
    {
        if ($bid->fulfilled_by === null) {
            return $bid->closed_at === null
                ? self::STATUS_PENDING
                : self::STATUS_REJECTED;
        }

        return (int) $bid->fulfilled_by === (int) $bid->rest_stop_id
            ? self::STATUS_SELECTED
            : self::STATUS_REJECTED;
    }
};
