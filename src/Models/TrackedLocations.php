<?php

namespace RecursiveTree\Seat\TerminusInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class TrackedLocations extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_terminusinv_tracked_locations';

    public function station(){
        return $this->hasOne(UniverseStation::class, 'station_id', 'location_id')->withDefault([
            'name' => trans('web::seat.unknown'),
        ]);
    }

    public function structure(){
        return $this->hasOne(UniverseStructure::class, 'structure_id', 'location_id')->withDefault([
            'name' => trans('web::seat.unknown'),
        ]);
    }
}