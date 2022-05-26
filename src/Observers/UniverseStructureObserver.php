<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Models\Location;

class UniverseStructureObserver
{
    public function saved($structure){
        $location = Location::where("structure_id",$structure->structure_id)->first();

        if($location == null){
            $location = new Location();
        }

        $location->structure_id = $structure->structure_id;
        $location->name = $structure->name;
        $location->save();
    }
}