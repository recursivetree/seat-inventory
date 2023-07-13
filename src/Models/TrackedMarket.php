<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;

class TrackedMarket extends Model
{
    public $timestamps = false;

    public function location(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Location::class, "id", "location_id");
    }

    protected $table = 'seat_inventory_tracked_markets';
}