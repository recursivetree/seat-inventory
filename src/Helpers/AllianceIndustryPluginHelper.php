<?php

namespace RecursiveTree\Seat\Inventory\Helpers;


use RecursiveTree\Seat\AllianceIndustry\Api\V1\ApiV1;

class AllianceIndustryPluginHelper
{
    /**
     * @return bool
     */
    public static function pluginIsAvailable(){
        return
            class_exists(self::$API);
    }

    public static $API = "RecursiveTree\Seat\AllianceIndustry\Api\AllianceIndustryApi";
}