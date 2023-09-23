<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\RefreshToken;

class TrackedMarket extends Model
{
    public $timestamps = false;

    protected $table = 'seat_inventory_tracked_markets';

    public function refresh_token() {
        return $this->hasOne(RefreshToken::class, "character_id", "character_id");
    }

    public function character() {
        return $this->hasOne(CharacterInfo::class, "character_id", "character_id");
    }

    public function location(){
        return $this->hasOne(Location::class, 'id', 'location_id');
    }
}