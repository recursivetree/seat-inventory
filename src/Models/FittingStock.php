<?php

namespace RecursiveTree\Seat\TerminusInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class FittingStock extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_terminusinv_fitting_stock';

    public function location(){
        return $this->hasOne(TrackedLocations::class, "id", "location_id");
    }

    public function items()
    {
        return $this->hasMany(FittingItem::class,"fitting_id","id");
    }
}