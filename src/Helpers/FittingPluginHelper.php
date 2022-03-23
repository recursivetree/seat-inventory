<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

class FittingPluginHelper
{
    public static function pluginIsAvailable(){
        return
            class_exists(self::$FITTING_PLUGIN_FITTING_MODEL)
            && class_exists(self::$FITTING_PLUGIN_DOCTRINE_MODEL);
    }

    public static $FITTING_PLUGIN_FITTING_MODEL = "Denngarr\Seat\Fitting\Models\Fitting";
    public static $FITTING_PLUGIN_DOCTRINE_MODEL = "Denngarr\Seat\Fitting\Models\Doctrine";
}