<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Helpers\LocationHelper;
use RecursiveTree\Seat\Inventory\Models\Location;

class UniverseStationObserver
{
    public function saved($station){
        $location = Location::where("station_id",$station->station_id)->first();

        if($location == null){
            $location = new Location();
        }

        $location->station_id = $station->station_id;
        $location->name = $station->name;
        $location->save();
    }
}