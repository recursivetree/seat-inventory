<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;

class TrackedAlliance extends Model
{
    public $timestamps = false;

    public function alliance(){
        return $this->hasOne(Alliance::class, "alliance_id", "alliance_id");
    }

    protected $table = 'seat_inventory_tracked_alliances';
    protected $primaryKey = 'alliance_id';
    public $incrementing = false;
}