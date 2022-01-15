<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

class FittingPluginHelper
{
    public static function pluginIsAvailable(){
        return class_exists(self::$FITTING_PLUGIN_FITTING_MODEL);
    }

    public static string $FITTING_PLUGIN_FITTING_MODEL = "Denngarr\Seat\Fitting\Models\Fitting";
}