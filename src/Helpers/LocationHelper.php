<?php

namespace RecursiveTree\Seat\TerminusInventory\Helpers;

use JetBrains\PhpStorm\ArrayShape;

class LocationHelper
{
    public bool $valid;
    public $station_id;
    public $structure_id;

    public function __construct($valid, $station_id, $structure_id) {
        $this->valid = $valid;
        $this->station_id = $station_id;
        $this->structure_id = $structure_id;
    }

    public static function parseLocationSuggestion($location){
        if($location==null){
            return new LocationHelper(false, null, null);
        }

        //check if the location is in a valid format
        $location_regexp = [];
        if (!preg_match("/^(?:station\|(?<station_id>\d+))|(?:structure\|(?<structure_id>\d+))$/",$location, $location_regexp)){
            return new LocationHelper(false, null, null);
        }

        $location_helper = new LocationHelper(true, null, null);

        //convert
        if(strlen($location_regexp["station_id"])>1){
            $location_helper->station_id = $location_regexp["station_id"];
        } else if(strlen($location_regexp["structure_id"])>1){
            $location_helper->structure_id = $location_regexp["structure_id"];
        }

        return $location_helper;
    }
}