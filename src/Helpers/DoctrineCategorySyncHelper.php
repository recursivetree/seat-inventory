<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;

class DoctrineCategorySyncHelper
{
    private static function categoryNameForDoctrine($doctrine){
        return "Doctrine: $doctrine->name";
    }

    public static function sync(){
        if(FittingPluginHelper::pluginIsAvailable()) {
            $stocks = Stock::where("fitting_plugin_fitting_id", "!=", null)->get();
            foreach ($stocks as $stock) {
                self::syncStock($stock);
            }
        }
    }

    public static function syncDoctrine($doctrine){
        if(FittingPluginHelper::pluginIsAvailable()) {
            //generate category for doctrine if it doesn't exist
            $category = self::getOrCreateDoctrineCategory($doctrine);

            //the name might have changed
            $category->name = self::categoryNameForDoctrine($doctrine);
            $category->save();

            //I'm to lazy to only update affected stock, so update all
            self::sync();
        }
    }

    public static function syncStock($stock){
        if(FittingPluginHelper::pluginIsAvailable()) {

            $fitting = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::find($stock->fitting_plugin_fitting_id);
            if ($fitting) {
                $doctrines = $fitting->doctrines;
                $categories = $stock->categories;

                $to_remove = $categories->whereNotIn("fitting_plugin_doctrine_id", $doctrines->pluck("id"));
                foreach ($to_remove as $category) {
                    $stock->categories()->detach($category->id);
                }

                $to_add = $doctrines->whereNotIn("id", $categories->pluck("fitting_plugin_doctrine_id"));
                foreach ($to_add as $doctrine) {
                    $category = self::getOrCreateDoctrineCategory($doctrine);
                    $category->stocks()->attach($stock->id);
                }
            }
        }
    }

    public static function getOrCreateDoctrineCategory($doctrine){
        $category = StockCategory::where("fitting_plugin_doctrine_id",$doctrine->id)->first();
        if(!$category){
            $category = new StockCategory();
            $category->fitting_plugin_doctrine_id = $doctrine->id;
            $category->name = self::categoryNameForDoctrine($doctrine);
            $category->save();
        }
        return $category;
    }

    public static function removeDoctrine($doctrine){
        $categories = StockCategory::where("fitting_plugin_doctrine_id",$doctrine->id)->get();

        foreach ($categories as $category){
            $category->stocks()->detach();
            $category->delete();
        }

        self::sync();
    }
}