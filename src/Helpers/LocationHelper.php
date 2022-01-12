<?php

namespace RecursiveTree\Seat\TerminusInventory\Helpers;

use JetBrains\PhpStorm\ArrayShape;
use RecursiveTree\Seat\TerminusInventory\Models\Location;

class LocationHelper
{
    public static function parseLocationSuggestion($location_str){
        if($location_str==null){
            return null;
        }

        //check if the location is in a valid format
        $location_regexp = [];
        if (!preg_match("/^(?:station\|(?<station_id>\d+))|(?:structure\|(?<structure_id>\d+))$/",$location_str, $location_regexp)){
            return new LocationHelper(false, null, null);
        }

        $location = new Location();

        //convert
        if(strlen($location_regexp["station_id"])>1){
            $location->station_id = $location_regexp["station_id"];
        } else if(strlen($location_regexp["structure_id"])>1){
            $location->structure_id = $location_regexp["structure_id"];
        }

        return $location;
    }

    public static function fromStationID($station_id){
        return Location::where("station_id",$station_id)->first();
    }

    public static function fromStructureID($station_id){
        return Location::where("station_id",$station_id)->first();
    }
}